<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class WithdrawalsUsdtReceipt extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'withdrawals_usdt_receipts';

    public function withdrawal() {
        return $this->belongsTo(
            UserWithdrawal::class, 
            "withdrawal_no", 
            "withdrawal_no"
        );
    }
    
}
