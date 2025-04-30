<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class RedpacketsV2 extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'redpackets_v2';

    // 业务员
    public function salesman() {
        return $this->belongsTo(User::class, "mobile", "salesman_mobile");
    }
    
}
