<?php

namespace App\Jobs;

use App\Models\User;
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

class ItemEarningManual implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_item_id;
    protected $last_one;

    /**
     * Create a new job instance.
     */
    public function __construct($user_item_id, $last_one)
    {
        $this->user_item_id = $user_item_id;
        $this->last_one = $last_one;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        // 上级收益比例
        $lv1_commission = floatval(setting("LV1_SUPERIOR_COMMISSION", 10.0));
        $lv2_commission = floatval(setting("LV2_SUPERIOR_COMMISSION", 3.0));
        $lv3_commission = floatval(setting("LV3_SUPERIOR_COMMISSION", 1.0));

        $userRepository = app(UserRepository::class);

        $record = UserItem::where("id", $this->user_item_id)->first();
        if (empty($record)) {
            return;
        }

        $item = $record->item;
        if (empty($item)) {
            return;
        }

        $user = $record->user;
        if (empty($user)) {
            return;
        }

        // 20230810: 针对设置了收益到期领取收益类型的商品进行处理
        if ($item->is_earning_at_end == 1) {
            $gain = floatval($item->gain_per_day * $item->gain_day_num * $record->amount);
        } else {
            $gain = floatval($item->gain_per_day * $record->amount);
        }
        

        // 检查购买用户是否已经退款
        if ($this->last_one) {
            $is_refund = MoneyLog::where("user_id", $user->id)
                ->where("log_type", 10)
                ->where("item_id", $item->id)
                ->where("user_item_id", $record->id)
                ->exists();
            if (!$is_refund) {
                // 如果未退款则退款
                $userRepository->addBalance([
                    "user_id" => $user->id,
                    "money" => $item->price,
                    "log_type" => 10,
                    "item_id" => $item->id,
                    "user_item_id" => $record->id,
                    "balance_type" => "balance"
                ]);
            }
        }
        
        

        // 购买用户获得收益
        $userRepository->addBalance([
            "user_id" => $user->id,
            "money" => $gain,
            "log_type" => 5,
            "item_id" => $item->id,
            "user_item_id" => $record->id
        ]);

        // 购买用户上级获得收益
        if ($user->lv1_superior_id) {
            $lv1_superior = User::find($user->lv1_superior_id);
            // 只有上级用户持有商品价格大于等于当前用户时，才能领取收益
            // 20230830: 把分佣里面之前有个限制是 
            // 我的商品总金额必须大于我的下级我才可以获得他的分佣奖励，这个限制去掉
             if ($lv1_superior->asset_value >= $user->asset_value) {
                // 上级用户
                $userRepository->addBalance([
                    "user_id" => $user->lv1_superior_id,
                    "money" => $gain * ($lv1_commission / 100),
                    "log_type" => 4,
                    "item_id" => $item->id,
                    "source_uid"=>$user->id,
                    "user_item_id" => $record->id
                ]);
             }
            
        }

        if ($user->lv2_superior_id) {
            $lv2_superior = User::find($user->lv2_superior_id);
            // 只有上上级用户持有商品价格大于等于当前用户时，才能领取收益
            // 20230830: 把分佣里面之前有个限制是 
            // 我的商品总金额必须大于我的下级我才可以获得他的分佣奖励，这个限制去掉
             if ($lv2_superior->asset_value >= $user->asset_value){
                // 上上级用户
                $userRepository->addBalance([
                    "user_id" => $user->lv2_superior_id,
                    "money" => $gain * ($lv2_commission / 100),
                    "log_type" => 4,
                    "item_id" => $item->id,
                    "source_uid"=>$user->id,
                    "user_item_id" => $record->id
                ]);
             }
        }

        if ($user->lv3_superior_id) {
            $lv3_superior = User::find($user->lv3_superior_id);
            // 只有上上上级用户持有商品价格大于等于当前用户时，才能领取收益
            // 20230830: 把分佣里面之前有个限制是 
            // 我的商品总金额必须大于我的下级我才可以获得他的分佣奖励，这个限制去掉
             if ($lv3_superior->asset_value >= $user->asset_value){
                // 上上上级用户
                $userRepository->addBalance([
                    "user_id" => $user->lv3_superior_id,
                    "money" => $gain * ($lv3_commission / 100),
                    "log_type" => 4,
                    "item_id" => $item->id,
                    "source_uid"=>$user->id,
                    "user_item_id" => $record->id
                ]);
             }
        }

        // 记录收益时间
        $record->last_earning_at = now();
        // 标记为已审核
        $record->status = 2;
        if ($this->last_one) {
            // 标记为已停止收益
            $record->stoped_at = now();
        }
        $record->save();
    }
}
