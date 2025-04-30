<?php

namespace App\Models;

use App\Repositories\UserRepository;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PrizeWheelReward extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'prize_wheel_rewards';

    public function coupon() {
        return $this->belongsTo(Coupon::class);
    }

    public function item() {
        return $this->belongsTo(Item::class);
    }

    // 分发奖品
    public function distributeReward($user_id) {
        if ($this->reward_type == 1) {
            // 优惠券
            $coupon = Coupon::find($this->coupon_id);
            UserCoupon::create([
                "user_id" => $user_id,
                "coupon_id" => $coupon->id,
                "status" => 0,
                "expire_at" => now()->addHours($coupon->expire_time)
            ]);
        } else if ($this->reward_type == 2) {
            // 产品
            $item = Item::find($this->item_id);
            // 计算sn
            $maxSn = UserItem::where("item_id", $item->id)
                ->where("user_id", $user_id)
                ->max("serial_number");
            $sn = intval($maxSn) + 1;
            UserItem::create([
                "user_id" => $user_id,
                "item_id" => $item->id,
                "earning_end_at" => now()->addDays($item->gain_day_num),
                "serial_number" => $sn,
                "last_earning_at" => now(),
                "amount" => 1
            ]);
        } else if ($this->reward_type == 3) {
            $userRepository = app(UserRepository::class);
            try {
                $userRepository->addBalance([
                    "user_id" => $user_id,
                    "money" => $this->cash_amount,
                    "log_type" => 21,
                    "balance_type" => "balance",
                ]);
            } catch (\Throwable $th) {
                throw $th;
            }
        } else {
            return false;
        }
    }
    
}
