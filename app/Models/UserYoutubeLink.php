<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserYoutubeLink extends Model
{
	use HasDateTimeFormatter;
    use SoftDeletes;

    protected $table = 'user_youtube_links';

    protected $fillable = [
        "user_id",
        "name",
        "link",
        "image",
        "status",
        "sort"
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    
}
