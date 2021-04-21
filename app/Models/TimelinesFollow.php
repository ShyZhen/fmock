<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimelinesFollow extends Model
{
    //
    protected $table = 'timelines_follow';

    protected $fillable = [
        'user_id', 'resource_id',
    ];
}
