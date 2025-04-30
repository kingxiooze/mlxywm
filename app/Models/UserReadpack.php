<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserReadpack extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'user_readpacks';

    protected $fillable = [
        "user_id",
        "code",
        "open_user",
        "opened_at",
        "freeze_amount",
        "amount"
    ];

    public function openUser() {
        return $this->belongsTo(User::class, "open_user");
    }
    
}
