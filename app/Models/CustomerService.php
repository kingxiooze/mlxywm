<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CustomerService extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'customer_services';

    protected $fillable = [
        "icon",
        "address",
        "account",
    ];
    
}
