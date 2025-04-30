<?php

namespace App\Models\Chat;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'chat_rooms';

    protected $fillable = [
        'name', 
        "user_id",
        'uplimit',
        'content',
        "avatar",
    ];

    public function members() {
        return $this->hasMany(Member::class, "room_id");
    }
    
}
