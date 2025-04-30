<?php

namespace App\Models;

use Carbon\Carbon;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

// status字段
// 0 => 未评价
// 1 => 已评价
// 2 => 已审核
// 3 => 审核拒绝

class UserItem extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'user_items';

    protected $fillable = [
        "user_id",
        "item_id",
        "earning_end_at",
        "serial_number",
        "last_earning_at",
        "amount"
    ];

    protected $appends = ['total_income'];
    
    public function item(){
        return $this->belongsTo(Item::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    protected function totalIncome(): Attribute {
        return Attribute::make(
            get:function(){
                return MoneyLog::where("user_item_id", $this->id)
                    ->where("user_id", $this->user_id)
                    ->where("log_type", 5)
                    // ->where("expired_at", ">", now())
                    ->sum("money");
            }
        );
    }

    // 收益状态
    protected function earningStatus(): Attribute {
        return Attribute::make(
            get:function(){
                if ($this->created_at->isSameDay(today())) {
                    // 当天购买，第二天才能领取
                    return 0;
                }

                // 最后一期
                $earning_end_at = new Carbon($this->earning_end_at);
                if (
                    now()->greaterThanOrEqualTo($earning_end_at) &&
                    now()->lessThanOrEqualTo($earning_end_at->addDay())
                ) {
                    
                    $is_gain_last = MoneyLog::where("user_item_id", $this->id)
                        ->where("log_type", 10)
                        ->exists();
                    if ($is_gain_last) {
                        // 已领取
                        return 2;
                    } else {
                        // 
                        return 1;
                    }
                } else if (
                    now()->greaterThanOrEqualTo($earning_end_at->addDay())
                ) {
                    // 收益结束
                    return 3;
                }

                $is_gain = MoneyLog::where("user_item_id", $this->id)
                    ->where("log_type", 5)
                    ->whereDate("created_at", today())
                    ->exists();
                if ($is_gain) {
                    // 已领取
                    return 2;
                } else {
                    // 未领取
                    return 1;
                }
            }
        );
    }
}
