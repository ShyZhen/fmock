<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostsCommentsLike extends Model
{
    //
    protected $table = 'posts_comments_like';

    protected $fillable = [
        'user_id', 'resource_id', 'action', 'type',
    ];
}
