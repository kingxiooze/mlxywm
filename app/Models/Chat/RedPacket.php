<?php

namespace App\Models\Chat;

use App\Models\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class RedPacket extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'chat_redpackets';

    protected $fillable = [
        "user_id",
        "record_id",
        "amount"
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    
}
