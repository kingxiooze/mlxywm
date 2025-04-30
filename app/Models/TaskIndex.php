<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class TaskIndex extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;
    protected $table = 'taskindex';
    protected $fillable = [
        "name",
    ];
    
    public function taskModels()
    {
        return $this->hasMany(TaskModel::class);
    }
     
}
