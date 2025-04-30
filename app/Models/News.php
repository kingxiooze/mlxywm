<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    public function item_category(){
        return $this->belongsTo(ItemCategory::class, "item_category_id");
    }
}
