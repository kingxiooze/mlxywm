<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PrizeWheelLog extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'prize_wheel_logs';

    protected $fillable = [
        "user_id",
        "reward_id",
        "name",
        "reward_type",
        "coupon_id",
        "item_id",
        "cash_amount"
    ];

    public function coupon() {
        return $this->belongsTo(Coupon::class);
    }

    public function item() {
        return $this->belongsTo(Item::class);
    }
    
}
