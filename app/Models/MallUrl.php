<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class MallUrl extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;
     protected $table = 'mallurl';
    protected $fillable = [
        "url",
        "wigth",
        "type",
        "serchtimes"
    ];

  
}
