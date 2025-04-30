<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\UserCashback;

// 充值返现

class RechargeCashback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // 充值用户ID
    protected $recharge_user_id;
    // 充值金额
    protected $amount;

    /**
     * Create a new job instance.
     */
    public function __construct($recharge_user_id, $amount)
    {
        $this->recharge_user_id = $recharge_user_id;
        $this->amount = $amount;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $recharge_user = User::where("id", $this->recharge_user_id)
            ->first();
        if (empty($recharge_user)) {
            return;
        }
        if (empty($recharge_user->parent)) {
            return;
        }

        // 检查当前用户充值金额是否比上级用户多
        // 如果更多，则不需要提供支付返现
        // 但是需要有记录

        if (
            $recharge_user->recharge_amount <=
            $recharge_user->parent->recharge_amount
        ) {
            // 充值返现比例(百分比)
            $rate = setting("RECHARGE_CASHBACK_RATE", 5);

            // 添加返现记录
            UserCashback::create([
                "user_id" => $recharge_user->parent->id,
                "pay_uid" => $recharge_user->id,
                "item_id" => 0,
                "pay_amount" => $this->amount,
                "back_amount" => $this->amount * floatval($rate / 100),
                "log_type" => 19
            ]);
        } else {
            // 添加记录，但是不返现
            UserCashback::create([
                "user_id" => $recharge_user->parent->id,
                "pay_uid" => $recharge_user->id,
                "item_id" => 0,
                "pay_amount" => $this->amount,
                "back_amount" => 0,
                "log_type" => 19
            ]);
        }

        
    }
}
