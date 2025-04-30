<?php

namespace App\Models\Chat;

use App\Models\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'chat_members';

    protected $fillable = [
        "room_id",
        "user_id",
        "is_mute"
    ];
    
    public function user() {
        return $this->belongsTo(User::class);
    }
}
