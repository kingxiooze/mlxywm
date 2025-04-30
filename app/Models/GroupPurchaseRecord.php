<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class GroupPurchaseRecord extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'group_purchase_records';

    protected $fillable = [
        "user_id",
        "item_id",
        "expired_at",
        "status",
        "amount"
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function item() {
        return $this->belongsTo(Item::class);
    }
    
}
