<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideosFollow extends Model
{
    //
    protected $table = 'videos_follow';

    protected $fillable = [
        'user_id', 'resource_id',
    ];
}
