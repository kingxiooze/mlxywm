<?php

namespace App\Repositories;

use App\Jobs\RechargeCashback;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\User;
use App\Models\Order;
use App\Models\TaskOrder;
use App\Models\MoneyLog;
use App\Models\UserWithdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class OrderRepository.
 *
 * @package namespace App\Repositories;
 */
class OrderRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Order::class;
    }

    

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    // 支付订单成功
    public function orderSuccess($order_no, $amount=0, $transaction_id=null, $trade_state=false) {
        // 查找订单是否存在
        $order = Order::where("order_no", $order_no)->first();
   
        if (!empty($order)) {
            // 如果应用内订单状态为未支付, 并且回调内状态为已支付时

            $orderPayStatus = $order->pay_status;
            if ($trade_state && $orderPayStatus == 2) {
                // 更新订单
                // 更新订单状态
                $data = [
                    "trade_no" => $transaction_id,
                    "money" => $amount,
                    "pay_time" => now(),
                    "pay_status" => 1,
                    "order_status" => 2,
                ];
                try {
                    DB::beginTransaction();
                    $order = Order::where("order_no", $order_no)
                        ->lockForUpdate()
                        ->first();
                    $order->update($data);
                    
                     
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                }
                // 20230803: 收到回调时，自动到账
                $this->orderPass($order);
            }
            
        }
    }

    // 提现订单成功
    public function withdrawalSuccess($order_no, $amount=0, $transaction_id=null, $trade_state=false) {
        // 查找订单是否存在
        $order = UserWithdrawal::where("withdrawal_no", $order_no)->first();
        if (!empty($order)) {
            // 如果应用内订单状态为未支付, 并且回调内状态为已支付时
            $orderPayStatus = $order->pay_status;
            if ($trade_state && $orderPayStatus == 2) {
                // 更新订单
                // 更新订单状态
                $data = [
                    "transaction_id" => $transaction_id,
                    // "money" => $amount,
                    "pay_time" => now(),
                    "pay_status" => 1,
                    "order_status" => 1,
                    "status" => 1
                ];
                try {
                    DB::beginTransaction();
                    $order = UserWithdrawal::where("withdrawal_no", $order_no)
                        ->lockForUpdate()
                        ->first();
                    $order->update($data);
                    
                    // 用户减少余额
                    // $userRepository = app(UserRepository::class);
                    // $userRepository->addBalance([
                    //     "user_id" => $order->user_id,
                    //     "money" => 0 - $amount,
                    //     "log_type" => 2
                    // ]);
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                }
            } else if ($trade_state == false && $orderPayStatus == 2) {
                $data = [
                    "transaction_id" => $transaction_id,
                    // "money" => $amount,
                    "pay_time" => now(),
                    "pay_status" => 1,
                    "order_status" => 3,
                    "status" => 2
                ];
                try {
                    DB::beginTransaction();
                    $order = UserWithdrawal::where("withdrawal_no", $order_no)
                        ->lockForUpdate()
                        ->first();
                    $order->update($data);
                    $order->afterRejected();

                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                }
            }
            
        }
    }

    // 订单人工处理(手动到账)
    public function orderPass($order) {
        log::info($order->tasknumber);
       $TaskOrder=   TaskOrder::where("number",$order->tasknumber)->first();
       
       $TaskOrder->status = 2;
       $TaskOrder->systemNo = $order->order_no;
       $TaskOrder->save();
       
         
    }
    
}
