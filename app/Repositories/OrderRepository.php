<?php

namespace App\Repositories;

use App\Jobs\RechargeCashback;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\User;
use App\Models\Order;
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
                    
                    // 20230803: 迁移至orderPass方法

                    // if ($order->pay_type == 2) {
                    //     $order->usdt->update(["status" => 1]);
                    // }

                    // if ($order->goods_type == 1) {
                    //     // 充值

                    //     if ($order->pay_type == 2) {
                    //         // 余额要根据支付方式不同进行换算
                    //         // 例如 1USDT = 90余额
                    //         $rate = (int) setting("USDT_EXCHANGE_RATE", 90);
                    //         $balance_amount = $amount * $rate;
                    //     } else {
                    //         $balance_amount = $amount;
                    //     }
                        
                    //     // 用户添加余额
                    //     $userRepository = app(UserRepository::class);
                    //     $userRepository->addBalance([
                    //         "user_id" => $order->user_id,
                    //         "money" => $balance_amount,
                    //         "log_type" => 1
                    //     ]);
                    //给上级用户添加返现且只添加一次
                    //查询是否是第一次充值。
                    
                    
                    //     // 为上级用户返现
                    //     RechargeCashback::dispatch($order->user_id, $balance_amount);
                    // } 
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                }
                // 20230803: 收到回调时，自动到账
                $this->orderPass($order_no, $amount);
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
    public function orderPass($order_no, $amount) {
        // 查找订单是否存在
        $order = Order::where("order_no", $order_no)->first();
        if (!empty($order)) {
            // 更新订单
            try {
                DB::beginTransaction();
                $order = Order::where("order_no", $order_no)
                    ->lockForUpdate()
                    ->first();
                if ($order->pay_type == 2) {
                    $order->usdt->update(["status" => 1]);
                }
                
                if ($order->goods_type == 1) {
                    // 充值
                        
                    if ($order->pay_type == 2) {
                        // 余额要根据支付方式不同进行换算
                        // 例如 1USDT = 90余额
                        $rate = (int) setting("USDT_EXCHANGE_RATE", 90);
                        $balance_amount = $amount * $rate;
                    } else {
                        $balance_amount = $amount;
                    }
                    //给上级添加返现100，先判断是否是第一次充值
                    $firstRecharge = MoneyLog::where("user_id",$order->user_id)
                    ->where("log_type",1)
                    ->exists();
                    if(!$firstRecharge){
                        //给上级增加余额
                    //     $user = User::where("id", $order->user_id)
                    //      ->first();
                    //     $userRepository2 = app(UserRepository::class);
                    //     $userRepository2->addBalance([
                    //     "source_uid" => $order->user_id,
                    //     "user_id"=>$user->lv1_superior_id,
                    //     "money" => 100,
                    //     "log_type" => 19
                    // ]);
                    $userRepository3 = app(UserRepository::class);
                    $userRepository3->addBalance([
                        "user_id"=>$order->user_id,
                        "money" => 100,
                        "log_type" => 23
                    ]);
                    }
                    // 用户添加余额
                    $userRepository = app(UserRepository::class);
                    $userRepository->addBalance([
                        "user_id" => $order->user_id,
                        "money" => $balance_amount,
                        "log_type" => 1
                    ]);
                    
                    // 标记为有效用户
                    $user = User::where("id", $order->user_id)
                        ->lockForUpdate()
                        ->first();
                    $user->is_recharged_or_buyed = 1;

                    // 20230920: 大转盘功能-根据充值金额增加抽奖次数
                    // 20230921: OYO 的抽奖功能
                    $unit = setting("PRIZE_COUNT_RECHARGE_UNIT", 100);
                    $append_prize_count = floor($balance_amount / $unit);
                    $user->prize_count += $append_prize_count;

                    $user->save();
                } 
                DB::commit();
            } catch (\Throwable $th) {
                report($th);
                DB::rollBack();
            }
            
        }
    }
    
}
