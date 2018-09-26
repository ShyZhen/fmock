<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersPostsLike extends Model
{
    //
    protected $table = 'users_posts_like';

    protected $fillable = [
        'user_id', 'post_id', 'action'
    ];
}
