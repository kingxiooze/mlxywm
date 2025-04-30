<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TaskOrder extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;
    protected $table = 'taskorder';
    protected $primaryKey = 'id';
    protected $fillable = [
        "id",
        "user_id",
        "model_index",
        "status",
        "task_id",
        "item_id",
        "price",
        "orderNo",
        "freeze_at"
    ];

     public function user() {
        return $this->belongsTo(User::class);
    }
     public function taskId() {
        return $this->belongsTo(TaskIndex::class,"task_id");
    }
    public function item() {
        return $this->belongsTo(Item::class);
    }
    

   
}
