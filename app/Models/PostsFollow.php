<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostsFollow extends Model
{
    //
    protected $table = 'posts_follow';

    protected $fillable = [
        'user_id', 'resource_id',
    ];
}
