<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserBankcard extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'user_bankcard';

    protected $fillable = [
        "user_id",
        'bank_name', 
        'card_no',
        'name',
        'mobile',
        'email',
        'ifsc_code',
        "subbranch",
        "wallet_address",
        "wallet_chain",
        "bank_code"
    ];
    
}
