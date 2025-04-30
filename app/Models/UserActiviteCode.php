<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserActiviteCode extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'user_activite_codes';

    protected $fillable = [
        "user_id",
        "code",
        "activite_user",
        "activited_at",
        "freeze_amount",
        "amount"
    ];
    
}
