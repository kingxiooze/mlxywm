<?php

namespace App\Jobs;

use App\Models\MoneyLog;
use App\Models\User;
use App\Models\UserItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckIsRechargeOrOwnItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $users = User::where("is_recharged_or_buyed", 0)->lazyById(200, "id");

        foreach ($users as $user) {
            // 检查是否充值
            $is_recharge = MoneyLog::where("log_type", 1)
                ->where("user_id", $user->id)
                ->exists();
            // 是否拥有商品
            $is_own_item = UserItem::where("user_id", $user->id)
                ->exists();
            if ($is_recharge || $is_own_item) {
                User::where("id", $user->id)->update(["is_recharged_or_buyed" => 1]);
            }
        }

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }
}
