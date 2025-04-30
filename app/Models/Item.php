<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;

class Item extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    // protected $appends = ['joined_count'];

    protected function joinedCount(): Attribute {
        return Attribute::make(
            get:function(){
                return GroupPurchaseRecord::where("item_id", $this->id)
                    // ->where("expired_at", ">", now())
                    ->count();
            }
        );
    }

    public function category(){
        return $this->belongsTo(ItemCategory::class, "category_id");
    }

    // 扣除库存
    public function costStock($target, $force = false) {
        if ($this->stock <= 0) {
            return false;
        }
        // 当库存数量小于扣除数量时
        if ($target > $this->stock) {
            if ($force) {
                $target = $this->stock;
            } else {
                return false;
            }
        }
        $this->stock -= $target;
        $this->save();
        return true;
    }
}
