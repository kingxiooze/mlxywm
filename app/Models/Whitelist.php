<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Whitelist extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $fillable = [
        "user_id",
        "item_id",
        "amount"
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function item() {
        return $this->belongsTo(Item::class);
    }
}
