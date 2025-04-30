<?php

namespace App\Jobs;

use App\Models\UserCashback;
use App\Models\UserItem;
use App\Models\MoneyLog;
use App\Repositories\UserRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
class ItemEarningJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("into");
        // 商品收益
        // 每小时触发一次收益
        // 20230717: 收益改为每小时触发
        $now = now()->startOfMinute();
        $ago = now()->subHour()->endOfMinute();
        
        // 上级收益比例
        // $lv1_commission = floatval(setting("LV1_SUPERIOR_COMMISSION", 10.0));
        // $lv2_commission = floatval(setting("LV2_SUPERIOR_COMMISSION", 3.0));
        // $lv3_commission = floatval(setting("LV3_SUPERIOR_COMMISSION", 1.0));

        $userRepository = app(UserRepository::class);

        $records = UserItem::where("earning_end_at", ">=", $now)
            ->where("last_earning_at", "<=", $ago)
            ->get();
         Log::info($records);
        foreach ($records as $record) {
            $item = $record->item;
            if (empty($item)) {
                continue;
            }

            $user = $record->user;
            if (empty($user)) {
                continue;
            }

            // 20230717: 收益改为每小时触发, 日收益金额 除以 24
            $gain = floatval($item->gain_per_day / 24);

            // 购买用户获得收益
            $userRepository->addBalance([
                "user_id" => $user->id,
                "money" => $gain,
                "log_type" => 5,
                "item_id" => $item->id,
                "user_item_id" => $record->id
            ]);
            // // 购买用户上级获得收益
            // if ($user->lv1_superior_id) {
            //     // 上级用户
            //     $userRepository->addBalance([
            //         "user_id" => $user->lv1_superior_id,
            //         "money" => $gain * ($lv1_commission / 100),
            //         "log_type" => 4,
            //         "item_id" => $item->id,
            //         "user_item_id" => $record->id
            //     ]);
            // }

            // if ($user->lv2_superior_id) {
            //     // 上上级用户
            //     $userRepository->addBalance([
            //         "user_id" => $user->lv2_superior_id,
            //         "money" => $gain * ($lv2_commission / 100),
            //         "log_type" => 4,
            //         "item_id" => $item->id,
            //         "user_item_id" => $record->id
            //     ]);
            // }

            // if ($user->lv3_superior_id) {
            //     // 上上上级用户
            //     $userRepository->addBalance([
            //         "user_id" => $user->lv3_superior_id,
            //         "money" => $gain * ($lv3_commission / 100),
            //         "log_type" => 4,
            //         "item_id" => $item->id,
            //         "user_item_id" => $record->id
            //     ]);
            // }

            // 记录收益时间
            $record->last_earning_at = now();
            $record->save();

            // 如果收益时间数量达到设置的收益时间则退还本金
            if (
                $record->created_at->endOfDay()->diffInDays(today()) >= $item->gain_day_num
            ) {
                // 检查购买用户是否已经退款
                $is_refund = MoneyLog::where("user_id", $user->id)
                    ->where("log_type", 10)
                    ->where("item_id", $item->id)
                    ->where("user_item_id", $record->id)
                    ->exists();
                if (!$is_refund) {
                    // 如果未退款则退款
                    // 退还本金
                    $userRepository->addBalance([
                        "user_id" => $user->id,
                        "money" => $item->price,
                        "log_type" => 10,
                        "item_id" => $item->id,
                        "user_item_id" => $record->id
                    ]);
                    // 删除记录
                    $record->delete();
                }
            }
        }
    }
}
