<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TaskModel extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;
    protected $table = 'taskmodel';
    protected $fillable = [
        "type",
        "number",
        "cmtype",
        "commission",
        "task_index_id",
        "item_price"
    ];

    
    // app/Models/TaskModel.php
public function taskIndex()
{
    return $this->belongsTo(TaskIndex::class);
}
}
