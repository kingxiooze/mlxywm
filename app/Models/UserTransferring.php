<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserTransferring extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'user_transferring';

    protected $fillable = [
        "user_id",
        "user_item_id",
        "to_user_id",
        "created_at"
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
     public function item() {
        return $this->belongsTo(Item::class);
    }
     public function sourceUser() {
        return $this->belongsTo(User::class, "to_user_id");
    }
}
