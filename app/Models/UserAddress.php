<?php

namespace App\Models;

use Carbon\Carbon;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

 
class UserAddress extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'user_address';

    protected $fillable = [
        "user_id",
        "address",
        "name",
        "phone",
     
    ];

    
}
