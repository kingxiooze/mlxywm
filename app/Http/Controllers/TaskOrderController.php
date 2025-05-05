<?php

namespace App\Http\Controllers;

use App\Models\TaskModel;
use App\Models\TaskOrder;
use App\Models\TaskIndex;
use App\Models\MoneyLog;
use App\Models\PayUsdt;
use App\Models\ItemCategory;
use App\Repositories\UserRepository;
use App\Services\Payment\Tool as PaymentTool;
use App\Models\Item;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class TaskOrderController extends Controller
{
    
    
    
   public function detailorder (Request $request) {
       
          $number = $request->input("number", null);
         $taskOrder = TaskOrder::with([
                    'user' => function ($query) {
                        $query->select('id','name', 'avatar'); // 只获取 item 表的 id、name、category_id
                    }
                ])->where("number",$number)->first();
                
          $taskOrder->sysnow = now()->toDateTimeString();        
          return $this->success($taskOrder);        
       
   }
    
     
     
     
       
    public function detail (Request $request) {
          $number = $request->input("number", null);
         $taskOrder=TaskOrder::where("number",$number)->first();
         $taskmodels = TaskModel::where("task_index_id", $taskOrder->task_id)
                ->with([
                    'itemIdinfo' => function ($query) {
                        $query->select('id','name', 'price', 'image',"description","category_id"); // 只获取 item 表的 id、name、category_id
                    },
                    'itemIdinfo.category' => function ($query) {
                        $query->select('id', 'name'); // 只获取 category 表的 id、name
                    }
                ])
                ->get();
        
         
         return $this->success(["taskmodel"=>$taskmodels]); 
    }
    //定时任务
     public function timersData () {
         //超出时间冻结
         $freeze_at =  TaskOrder::where("status",0)->where('freeze_at', '<', now())->update(['status' => 3]);
         
         
         
          
         //
         return $this->success($freeze_at); 
         
     }
   public function postsureOrders (Request $request) {
      $id = $request->input("id", null);
      $user =  auth()->user();
      $TaskOrder = TaskOrder::find($id);
      $TaskModel = TaskModel::where("task_index_id",$TaskOrder->task_id)->where("number",$TaskOrder->model_index)->first();
      
      
        $lock = Cache::lock("postsureOrders:" . $user->id, 10);
          DB::beginTransaction();
        try {
       $TaskOrder->status = 2;
       $TaskOrder->save();
      //加本金
     // $user->balance +=  $TaskOrder->price;
         //增加记录
       $userRepository = app(UserRepository::class);
        //添加转出用户资金记录
        $userRepository->addBalance([
                "user_id" => $user->id,
                "money" => $TaskOrder->price,
                "log_type" => 10,
                "balance_type" => "balance",
            ]);  
         //加佣金
        $commission = $TaskOrder->price*($TaskModel->commission/100);
    //   $user->balance +=  $commission;
    //   $user->save();  
         //加记录   
        $userRepository = app(UserRepository::class);
        //添加转出用户资金记录
        $userRepository->addBalance([
                "user_id" => $user->id,
                "money" => $commission,
                "log_type" => 5,
                "balance_type" => "balance",
            ]);    
            DB::commit();
        } catch (LockTimeoutException $th) {
            DB::rollBack();
            throw new \Exception("系统故障.");
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new \Exception($th->getMessage());
        } finally {
            $lock?->release();
        }   
     return $this->ok(); 
   }
    
    
    public function payorder (Request $request) {
         $id = $request->input("id", null);
         $user =  auth()->user();
         //校验交易密码
           try {
            $user->checkTradePassword();
            } catch (\Throwable $th) {
                return $this->errorBadRequest($th->getMessage());
            }
         //查询订单
         $TaskOrder = TaskOrder::find($id);
         if(empty($TaskOrder)){
           return $this->errorBadRequest("Order not found");  
         }
         //比对余额
         if($user->balance<$TaskOrder->price){
             return $this->errorBadRequest("Insufficient balance");  
         }
         $lock = Cache::lock("payorder:" . $user->id, 10);
          DB::beginTransaction();
        try {
         //修改状态  
         $TaskOrder->status = 1;
         $TaskOrder->save();
         //减余额
        //  $user->balance-= $TaskOrder->price;
        //  $user->save();
         
         //增加记录
         $userRepository = app(UserRepository::class);
        //添加转出用户资金记录
        $userRepository->addBalance([
                "user_id" => $user->id,
                "money" => 0 - $TaskOrder->price,
                "log_type" => 6,
                "balance_type" => "balance",
            ]);
         
              DB::commit();
        } catch (LockTimeoutException $th) {
            DB::rollBack();
            throw new \Exception("系统故障.");
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new \Exception($th->getMessage());
        } finally {
            $lock?->release();
        }
         
         
          return $this->success($TaskOrder); 
        
    }
    public function postOrder () {
        //查询用户是否分配任务
         $user =  auth()->user();
         if(empty($user->task_id)){
              return $this->errorBadRequest("Task permissions are not enabled, please contact customer service");
         }
         
         if(($user->balance<20)){
              return $this->errorBadRequest("Account balance less than $20 cannot accept tasks");
         }
         $createOrder=false;
         //如果有任务就查询最新任务是否完成
         $TaskOrder = TaskOrder::where("user_id",$user->id)->where("task_id",$user->task_id)->orderBy("id","desc")->first();
         $TaskModelCount = TaskModel::where("task_index_id",$user->task_id)->count();
         $number = 1;
         //没有任务就从第一个任务开始
         if(!empty($TaskOrder)){
            //判断订单是否是最后一个
            $number = $TaskOrder->model_index+1;
            Log::info("number:".$number."index________".$TaskOrder->model_index);
            if($TaskOrder->status==1){
                  return $this->errorBadRequest("The order has been paid and is awaiting settlement. Please wait.");
            }
            if($TaskOrder->status==2){
                $createOrder=true;
            }else{
                return $this->success($TaskOrder); 
            }
           if($TaskOrder->model_index==$TaskModelCount&&$user->is_openwallet!=1){
                return $this->errorBadRequest("All tasks completed");
            }
           
         }
           
           if($user->is_openwallet==1){
                $createOrder=true;
                $number=1;
            }
          $TaskModel = TaskModel::where("task_index_id",$user->task_id)->where("number",$number)->first();
          if(empty($TaskModel)) return $this->errorBadRequest("Amazon order query error, please contact customer service");
          
          //查询当前用户余额 来匹配 商品
           
         
          //先查询类型
          $ItemCategory = ItemCategory::where("min_price","<=",$user->balance)->where("max_price",">",$user->balance)->first();
         
          $item = Item::where("category_id",$ItemCategory->id)->inRandomOrder()->first();
          
           $outTradeNo = PaymentTool::generateOutTradeNo();
          $user->is_openwallet = 0;
          $user->save();
          $TaskOrder= TaskOrder::create([
                    "user_id" => $user->id,
                    "model_index" => $number,
                    "task_id"=>$user->task_id,
                    "item_id"=>$item->id,
                    "orderNo" => $outTradeNo,
                    "price" => sprintf("%.2f", floatval($user->balance)*floatval($TaskModel->item_price)),
                   "freeze_at" => Carbon::now()->addHours(2)
                ]);  
        
         
        return $this->success($TaskOrder);
    }
}
