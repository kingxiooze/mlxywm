<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserRealname extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'user_realnames';

    protected $fillable = [
        "image1",
        "image2",
        "paper_type",
        "paper_code",
        "status"
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    
}
