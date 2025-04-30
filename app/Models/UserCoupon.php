<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserCoupon extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'user_coupons';

    protected $fillable = [
        "user_id",
        "coupon_id",
        "status",
        "expire_at",
        "item_id"
    ];

    public function item(){
        return $this->belongsTo(Item::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function coupon(){
        return $this->belongsTo(Coupon::class);
    }
    
}
