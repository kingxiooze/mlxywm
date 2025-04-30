<?php

namespace App\Models\Chat;

use App\Models\User;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Record extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'chat_records';

    protected $casts = [
        'content' => 'array',
    ];

    protected $fillable = [
        'room_id', 
        'user_id',
        'content',
        'record_type',
        'redpacket_amount',
        'redpacket_count',
    ];

    protected $appends = ['is_redpacket_opened'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function speaker() {
        return $this->belongsTo(User::class, "user_id")->select("id", "avatar", "name");
    }

    public function room() {
        return $this->belongsTo(Room::class);
    }

    protected function isRedpacketOpened(): Attribute {
        return Attribute::make(function(){
            if ($this->record_type != 2) {
                return false;
            }
            if (!auth()->check()) {
                return false;
            }
            $user = auth()->user();

            return RedPacket::where("user_id", $user->id)
                ->where("record_id", $this->id)
                ->exists();
        });
        
    }
    
}
