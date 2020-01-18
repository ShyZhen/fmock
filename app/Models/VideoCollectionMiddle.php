<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoCollectionMiddle extends Model
{
    //
    protected $table = 'video_collect_middle';

    protected $fillable = [
        'video_id', 'video_collection_id', 'sort_index',
    ];
}
