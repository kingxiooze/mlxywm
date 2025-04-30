<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SignLog extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'sign_log';
    public $timestamps = false;

    protected $fillable = [
        "user_id",
        "reward",
        "signed_at",
        "duration_day",
    ];

}
