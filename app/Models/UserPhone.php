<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserPhone extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;
    protected $table = 'user_phone';
    protected $fillable = [
        "numbers",
        "phone",
        "sfen_id",
        "remark"
    ];
    
     public function sfenIds()
    {
        return $this->belongsTo(UserSfen::class, 'sfen_id');
    }
     
}
