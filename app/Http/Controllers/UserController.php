<?php

namespace App\Http\Controllers;

use App\Models\UserCashback;
use App\Models\Item;
use App\Models\Order;
use App\Models\MoneyLog;
use App\Models\UserActiviteCode;
use App\Models\UserBankcard;
use App\Models\UserItem;
use App\Models\UserReadpack;
use App\Models\UserWithdrawal;
use App\Models\UserTransferring;
use App\Models\User;
use App\Models\UserAddress;
use App\Repositories\UserRepository;
use App\Repositories\SMSRepository;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    protected function getRepositoryClass(){
        return UserRepository::class;
    }
     
    
    
    
    
     public function postaddress(Request $request) {
        $address = $request->input("address", null); //转增手机号
        $name = $request->input("name", null); //转增手机号
        $phone = $request->input("phone", null); //转增手机号
        
        //先查询用户是否有
       $UserAddress= UserAddress::where("user_id", auth()->id())->first();
        if(empty($UserAddress)){
           $UserAddress= UserAddress::create([
                    "user_id" => auth()->id(),
                    "address" => $address,
                    "name"=>$name,
                    "phone"=>$phone,
                ]);    
        }else{
          $UserAddress-> name =  $name;
          $UserAddress-> phone =  $phone;
          $UserAddress-> address =  $address;
          $UserAddress->save();
        }
        
         
            
        return $this->ok();
    }
    public function getaddress(Request $request) {
      
        return $this->success(UserAddress::where("user_id", auth()->id())->first());
    }
    
    
    
    
      // 查询店家信息
    public function getBuyList(Request $request) {
        $user = auth()->user();
        $userInfo = User::where("code",$user->salesman_code) 
        ->first();
        return $this->success([
            "avatar"=>$userInfo->avatar,
             "name"=>$userInfo->name,
             "wx_number"=>$userInfo->wx_number,
             "qq_number"=>$userInfo->qq_number,
            ]);
    }
      // 商品转增记录
    public function getTransferring(Request $request) {
        $user_id = auth()->id();
         $query = UserTransferring::where("user_id", $user_id)
            ->with("sourceUser:id,name,avatar")
            ->with("item")
            ->orderBy("created_at", "desc")
           ->paginate(10);
        return $this->success($query);
    }
    // 收益记录
    public function getMyIncomeRecord(Request $request) {
        $user_id = auth()->id();
        $logs = MoneyLog::with(["item", "userItem"])
            ->where("log_type", 5)
            ->where("user_id", $user_id)
            ->orderBy("created_at", "desc")
            ->paginate(10);
        return $this->success($logs);
    }
    //转增余额
    public function postUserBalanceTf(Request $request){
        $toMobile = $request->input("toMobile", null); //转增手机号
        $amount = $request->input("amount", null); //转增数量
        $user = auth()->user();
        $lock = Cache::lock("BALANCETF_USER:" . $user->id, 10);
        
            //查询用户是否拥有当前藏品
        
         //查询受赠用户
        $toUser=User::where("mobile",$toMobile)
        ->first();
        if(empty($toUser)){
           return $this->errorBadRequest("查询不到受赠用户手机号"); 
        }
        DB::beginTransaction();
        try {
            //给用户表增加锁
            $user = User::lockForUpdate()
            ->where("id",$user->id)
            ->first();
            if($user->balance<$amount){
             return $this->errorBadRequest("你所拥有的钻石数量不足");
            }
        //减少用户余额
        $user->balance -= $amount;
        $user->save();    
        
        //增加受赠者余额
        $toUser->balance -= $amount;
        $toUser->save();    
       $userRepository = app(UserRepository::class);
        //添加转出用户资金记录
        $userRepository->addBalance([
                "user_id" => $user->id,
                "money" => 0 - $amount,
                "log_type" => 50,
                "source_uid" =>$toUser->id,
                // 20230614: 使用可提现余额
                "balance_type" => "balance",
            ]);
         //添加转入用户资金记录
        $userRepository->addBalance([
                "user_id" => $toUser->id,
                "money" =>  $amount,
                "log_type" => 50,
                "source_uid" => $user->id,
                // 20230614: 使用可提现余额
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
    
    //用户转增
     public function postUserTransferring(Request $request) {
        $useritemid = $request->input("useritemid", null);
        $toMobile = $request->input("toMobile", null);
        $user_id = auth()->id();
        $user =  auth()->user();
        //普通用户20天以后才可以转增
        if($user->is_salesman==0){
            return $this->errorBadRequest("转增暂未开启,请耐心等待"); 
        }
        
         //查询受赠用户
        $toUser=User::where("mobile",$toMobile)
        ->first();
        if(empty($toUser)){
           return $this->errorBadRequest("查询不到受赠用户手机号"); 
        }
        
        $lock = Cache::lock("TRANSFERRING_USER:" . $user_id, 10);
        $userItem = UserItem::where("id",$useritemid)
        ->where("user_id",$user_id)
        ->first();
            //查询用户是否拥有当前藏品
        if(empty($userItem)){
             return $this->errorBadRequest("藏品为空");
        }
        DB::beginTransaction();
        try {
        //减少用户库存，如果数量只有1 就假删 大于1就减1
        if($userItem->amount==1){
            $userItem->deleted_at = now();
        }else{
            $userItem->amount -= 1;     
        }
        $userItem->save();
       
        
        //被赠送者添加藏品
        //如果被赠送者有就增加数量，没有就新增
        $touserItem = UserItem::where("user_id",$toUser->id)
        ->where("item_id",$userItem->item_id)
        ->first();
        if(empty($touserItem)){
            $touserItem = UserItem::create([
                    "user_id" => $toUser->id,
                    "item_id" => $userItem->item_id,
                    "earning_end_at" => $userItem->earning_end_at,
                    "serial_number" => $userItem->serial_number,
                    "last_earning_at" => now(),
                    "amount" => 1
                ]);
        }else{
           $touserItem->amount += 1;
           $touserItem->save();
        }
        
        //添加记录
        
         $addTrans = UserTransferring::create([
                    "user_id" => $user_id,
                    "user_item_id" => $touserItem->id,
                    "created_at" => now(),
                    "to_user_id" => $toUser->id 
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

    // 收益统计
    public function getMyIncomeState(Request $request) {
        $user_id = auth()->id();

        $total_income = 0;
        $settled_income = 0;
        $unsettled_income = 0;

        $my_items = UserItem::where("user_id", $user_id)
            ->get();

        foreach ($my_items as $my_item) {
            $total_income += floatval($my_item->item->gain_per_day);
            if ($my_item->status == 2) {
                $settled_income += floatval($my_item->item->gain_per_day);
            } else {
                $unsettled_income += floatval($my_item->item->gain_per_day);
            }
        }
        
        return $this->success([
            "total_income" => $total_income,
            "settled_income" => $settled_income,
            "unsettled_income" => $unsettled_income,
        ]);
    }

    // 商品列表
    public function getMyDevice(Request $request) {
       $user_id = auth()->id();
        // 是否拼团
        $is_gp = $request->input("is_gp", "0");
        // 详情ID
        $id = $request->input("id", null);
        // 是否过期
        $is_expired = $request->input("is_expired", 0);

        $query = UserItem::where("user_id", $user_id)
            ->with("item")
            ->orderBy("created_at", "desc");
        if ($is_gp == "1") {
            $query = $query->whereHas("item", function (Builder $query) {
                $query->where('is_group_purchase', 1);
            });
        }

        if ($is_expired == 1) {
            $query = $query->whereNotNull("stoped_at");
        }

        if (!empty($id)) {
            $query = $query->where("id", $id);
        }

        $paginate = $query->paginate(10);
        $paginate->getCollection()->transform(function ($value) {
            $value->append("earning_status");
            return $value;
        });

        // $now = now()->toDateTimeString();
        $now = now()->getTimestamp();
        return $this->success([
            "devices" => $paginate,
            "now" => $now
        ]);
    }

    // 修改密码
    public function postChangePassword(Request $request) {
        $sms_code = $request->input("sms_code", null);
        // $old_password = $request->input("old_password", null);
        $new_password = $request->input("new_password", null);
        $confirm_password = $request->input("confirm_password", null);

        $user = auth()->user();

        // 20230810: 密码修改增加短信验证
        $smsRepository = app(SMSRepository::class);

        try {
            // 验证短信
            $smsRepository->checkVerifySms($user->mobile, $sms_code);
        } catch (\Throwable $th) {
            return $this->errorBadRequest($th->getMessage());
        }
        // 2200810: 旧密码就不要了
        // $isPass = Hash::check($old_password, $user->password);
        // if (!$isPass) {
        //     return $this->errorBadRequest("old password is error");
        // }

        $isConfirmed = $new_password == $confirm_password;
        if (!$isConfirmed) {
            return $this->errorBadRequest("confirmed password is error");
        }

        $user->password = Hash::make($new_password);
        $user->save();

        return $this->ok();

        
    }

    // 修改交易密码
    public function postChangeTradePassword(Request $request) {
        $old_password = $request->input("old_password", null);
        $new_password = $request->input("new_password", null);
        $confirm_password = $request->input("confirm_password", null);

        $user = auth()->user();
        if (!empty($old_password)) {
            $isPass = Hash::check($old_password, $user->trade_password);
            if (!$isPass) {
                return $this->errorBadRequest("old password is error");
            }
        }
        
        $isConfirmed = $new_password == $confirm_password;
        if (!$isConfirmed) {
            return $this->errorBadRequest("confirmed password is error");
        }

        $user->trade_password = Hash::make($new_password);
        $user->save();

        return $this->ok();

        
    }

    // 添加或修改银行卡
    public function postCUBankcard(Request $request) {
        $data = $request->validate([
            'bank_name' => "", 
            'card_no' => "",
            'name' => "",
            'mobile' => "",
            'email' => "",
            'ifsc_code' => "",
            "subbranch" => "",
            "wallet_chain" => "",
            "wallet_address" => "",
            "bank_code" => "",
            // 20230809: gold 修改银行卡需要注册手机的短信验证 
            //"sms_code" => "required",

        ]);

        $user = auth()->user();

        // $smsRepository = app(SMSRepository::class);

        // try {
        //     // 验证短信
        //     $smsRepository->checkVerifySms($user->mobile, $data["sms_code"]);
        // } catch (\Throwable $th) {
        //     return $this->errorBadRequest($th->getMessage());
        // }

        // 20230713: 提现银行卡 要保证银行卡唯一，真实姓名唯一
        // 20230810: 同步服务器代码改动
        // $is_cardno_exists = UserBankcard::where("card_no", $data["card_no"])
        //         ->exists();
        // if ($is_cardno_exists) {
        //     return $this->errorBadRequest("card no is exists");
        // }
        // $is_name_exists = UserBankcard::where("name", $data["name"])
        //         ->exists();
        // if ($is_name_exists) {
        //     return $this->errorBadRequest("name is exists");
        // }

        $user_id = $user->id;
        $card_id = $request->input("id", null);
        if (empty($card_id)) {
            // 新建
            $card = UserBankcard::where("card_no", $data["card_no"])
                ->where("user_id", $user_id)
                ->first();
            if ($card) {
                return $this->errorBadRequest("card is exists");
            }

            $data["user_id"] = $user_id;
            $card = UserBankcard::create($data);
        } else {
            // 修改
            $card = UserBankcard::where("id", $card_id)
                ->first();
            if (empty($card)) {
                return $this->errorNotFound("card not exists");
            }
            $card->fill($data)->save();
        }
        
        return $this->success($card);
    }

    // 获取银行卡
    public function getMyBankcard() {
        $user_id = auth()->id();
        $cards = UserBankcard::where("user_id", $user_id)
            ->orderBy("created_at", "asc")
            ->get();
        return $this->success($cards);
    }

    // 获取我的个人信息
    public function getMyInfo() {
        $data = $this->repository->find(
            auth()->id(),
            [
                "id",
                "name",
                "avatar",
                "mobile",
                "code",
                "parent_code",
                "balance",
                "redpacket_balance",
                "mission_balance",
                "is_salesman",
                "salesman_code",
                "trade_password",
                // 20230919: 增加total_invite
                "total_invite",
                "is_salesman"
            ]
        );
        
        $this->repository->refreshLastLoginTime(auth()->id());

        return $this->success(
            $data->append("available_balance")
                ->append("is_has_trade_password")
                ->makeHidden("trade_password")
                ->append("is_realname")
                ->makeHidden("realname")
                ->append("is_remember_trade_password")
                
        );
    }

    // 获取指定用户的个人信息
    public function getUserInfo(Request $request) {
        $user_id = $request->input("user_id", auth()->id());
        $data = $this->repository->find(
            $user_id,
            [
                "id",
                "name",
                "avatar",
                "mobile",
                "code",
                "parent_code",
                "balance",
                "redpacket_balance",
                "mission_balance",
                "is_salesman",
                "salesman_code",
            ]
        );

        return $this->success(
            $data->append("available_balance")
        );
    }

    // 获取我的流水
    public function getMyMoneylog(Request $request) {
        $type=$request->input("type", null);
        $paginate = MoneyLog::where("user_id", auth()->id())
            ->with("item")
            ->with("sourceUser")
            ->orderBy("created_at", "desc");
            
         if($type){
             $paginate = $paginate->where("log_type",$type)
             ->paginate(10);
         }else{
             $paginate = $paginate->paginate(10);
         }
        return $this->success($paginate);
    }
    // 获取我的流水统计
    public function getMyMoneyState() {
        $paginate = MoneyLog::where("user_id", auth()->id())
            ->with(["item"])
            ->orderBy("created_at", "desc")
            ->paginate(10);
        $product_income = MoneyLog::where("user_id", auth()->id())
            ->where("log_type",5)
            ->sum("money");
        $today_income=MoneyLog::where("user_id", auth()->id())
            ->whereIn("log_type",[5,3,4,22,14,23])
            ->whereDate("created_at",today())
            ->sum("money");
        $total_income=MoneyLog::where("user_id", auth()->id())
            ->whereIn("log_type",[5,3,4,22,14,23])
            ->sum("money");
        $team_income=MoneyLog::where("user_id", auth()->id())
            ->whereIn("log_type",[3,4,14])
            ->sum("money");
        return $this->success([
            "product_income"=>$product_income,
            "today_income"=>$today_income,
            "total_income"=>$total_income,
            "team_income"=>$team_income
        ]);
    }

    // 获取我的提现记录
    public function getMyWithdrawalLog(){
        $paginate = UserWithdrawal::where("user_id", auth()->id())
            ->select(
                "id", "user_id", "status", "amount", 
                "bankcard_id", "created_at", "pay_time"
            )
            ->with("bankcard")
            ->orderBy("created_at", "desc")
            ->paginate(10);
        return $this->success($paginate);
    }

    // 获取我的充值记录
    public function getMyRechargeLog(){
        $paginate = Order::where("user_id", auth()->id())
            ->select(
                "id", "user_id", "order_no", "pay_type", "price", 
                "created_at", "pay_time", "pay_status", "order_status"
            )
            ->orderBy("created_at", "desc")
            ->paginate(10);
        $paginate->getCollection()->transform(function ($value) {
            if ($value->pay_type == 2) {
                $value->pay_type = "usdt";
            } else {
                $value->pay_type = "bank";
            }

            if ($value->pay_status == 1 && $value->order_status == 2) {
                $value->status = 1;
            } else {
                $value->status = 0;
            }
            unset($value->pay_status);
            unset($value->order_status);
            return $value;
        });
        return $this->success($paginate);
    }

    // 我的个人统计
    public function getMyPersonalState(){
        $user_id = auth()->id();
        // 总收益
        $income = MoneyLog::where("user_id", $user_id)
            ->whereIn("log_type", [3, 4 ,5, 7, 8])
            ->sum("money");
            
        //product_income
        $product_income_today=MoneyLog::where("user_id", $user_id)
            ->where("log_type", 5)
            ->whereDate("created_at", today())
            ->sum("money");
        $product_income_total=MoneyLog::where("user_id", $user_id)
            ->where("log_type", 5)
            ->sum("money");
        // 今日收益
        $today_income = MoneyLog::where("user_id", $user_id)
            ->whereIn("log_type", [3, 4 ,5, 7, 8])
            ->whereDate("created_at", today())
            ->sum("money");
        // 未过期的产品数
        $product = UserItem::where("user_id", $user_id)
            ->where("earning_end_at", ">=", now())
            ->count();
        return $this->success([
            "income" => $income,
            "today_income" => strval($today_income),
            "product" => $product,
            "product_income_today"=>$product_income_today,
            "product_income_total"=>$product_income_total
        ]);
    }

    // 我的详细统计
    public function getMyDetailState(){
        $user = auth()->user();
        $user_id = $user->id;
        // 20230811: 接口优化：接口去掉商品收益和team_income  两个数据的统计
        // 产品收益
        // $product_income = MoneyLog::where("user_id", $user_id)
        //     ->where("log_type", 5)
        //     ->sum("money");
        // 充值统计
        $total_recharge = MoneyLog::where("user_id", $user_id)
            ->where("log_type", 1)
            ->sum("money");
        // 团队佣金
        // $team_income = MoneyLog::where("user_id", $user_id)
        //     ->where("log_type", 4)
        //     ->sum("money");
        // 今日充值
        $today_recharge = MoneyLog::where("user_id", $user_id)
            ->where("log_type", 1)
            ->whereDate("created_at", today())
            ->sum("money");
        
        return $this->success([
            // "product_income" => $product_income,
            "total_recharge" => $total_recharge,
            // "team_income" => $team_income,
            "today_recharge" => $today_recharge,
            // 任务金余额
            "mission_balance" => $user->mission_balance
        ]);
    }

    // 修改我的个人信息
    public function postEditMyInfo(Request $request) {
        $name = $request->input("name", null);
        $avatar = $request->input("avatar", null);

        $user = auth()->user();
    
        if (!empty($name)) {
            $user->name = $name;
        }

        if (!empty($avatar)) {
            $user->avatar = $avatar;
        }

        $user->save();

        return $this->ok();
    }

    // 使用激活码激活用户
    public function postActiviteUser(Request $request) {
        $code = $request->input("code", null);

        $user = auth()->user();

        $lock = Cache::lock("ACTIVITE_USER:" . $user->id, 10);
        
        DB::beginTransaction();
        try {
            
            if (empty($code)) {
                return $this->errorBadRequest("code is empty");
            }
    
            $record = UserActiviteCode::where("code", $code)->first();
            if (empty($record)) {
                return $this->errorNotFound("code is not exists");
            }
    
            if ($record->activited_at) {
                return $this->errorBadRequest("code is used");
            }
            
            $is_activite = UserActiviteCode::where("activite_user", $user->id)
                ->exists();
            if ($is_activite) {
                return $this->errorBadRequest("your account is activited");
            }
    
            $record->activite_user = $user->id;
            $record->activited_at = now();
            $record->save();
    
            // 为上级提供邀请红包奖励
            $cash = setting("REDPACK_CASH_AMOUNT", 2);
            $this->repository->addBalance([
                "user_id" => $user->lv1_superior_id,
                "money" => $cash,
                "log_type" => 11,
                "balance_type" => "balance",
                "source_uid" => $user->id
            ]);

            DB::commit();
        } catch (LockTimeoutException $th) {
            DB::rollBack();
            throw new \Exception("duplicate request, retry again please.");
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new \Exception($th->getMessage());
        } finally {
            $lock?->release();
        }
        

        return $this->ok();
    }

    // 查询是否激活
    public function getActiviteStatus () {
        $user = auth()->user();
        $is_activite = UserActiviteCode::where("activite_user", $user->id)
            ->exists();
        return $this->success(["is_activite" => $is_activite]);
    }

    // 获取实名信息
    public function getRealname () {
        $user = auth()->user();
        
        return $this->success($user->realname);
    }

    // 添加或者修改实名信息
    public function postRealname (Request $request) {
        $data = $request->validate([
            'image1' => "required", 
            'image2' => "required",
            'paper_type' => "required",
            'paper_code' => "required",
        ]);

        $user = auth()->user();

        if ($user->realname) {
            $user->realname->update($data);
        } else {
            $user->realname()->create($data);
        }

        return $this->ok();

    }

    // 我的提现统计
    public function getMyWithdrawalState(){
        $user = auth()->user();
        $user_id = $user->id;

        // 总充值
        $total_withdrawal = MoneyLog::where("user_id", $user_id)
            ->whereIn("log_type", [2, 24])
            ->sum("money");
        // 今日提现
        $today_withdrawal = MoneyLog::where("user_id", $user_id)
            ->whereIn("log_type", [2, 24])
            ->whereDate("created_at", today())
            ->sum("money");
        
        return $this->success([
            "total_withdrawal" => $total_withdrawal,
            "today_withdrawal" => $today_withdrawal,
            // 可提现余额
            "balance" => $user->balance
        ]);
    }
}
