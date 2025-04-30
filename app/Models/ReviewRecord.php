<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ReviewRecord extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'review_records';

    protected $fillable = [
        "user_item_id",
        "tmpl_id",
        "user_id",
        "image",
        "content",
        "status",
    ];

    public function tmpl(){
        return $this->belongsTo(ReviewTmpl::class, "tmpl_id");
    }

    public function user_item(){
        return $this->belongsTo(UserItem::class, "user_item_id");
    }
    
}
