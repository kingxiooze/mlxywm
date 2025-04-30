<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class RedpacketsV2Log extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'redpackets_v2_logs';

    protected $fillable = [
        "user_id",
        "redpacket_id",
        "amount"
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    
}
