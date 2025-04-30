<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class InviteRewardLog extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'invite_reward_logs';

    protected $fillable = [
        "user_id",
        "setting_id",
        "reward"
    ];
    
}
