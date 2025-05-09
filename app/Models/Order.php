<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $fillable = [
        "order_no",
        "trade_no",
        "user_id",
        "pay_type",
        "goods_type",
        "money",
        "price",
        "pay_status",
        "order_status",
        "pay_time",
        "payable_id",
        "payable_type",
        "tasknumber",
    ];

    public function payable()
    {
        return $this->morphTo();
    }

    public function usdt() {
        return $this->hasOne(OrderUsdt::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
