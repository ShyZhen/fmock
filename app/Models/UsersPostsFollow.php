<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersPostsFollow extends Model
{
    //
    protected $table = 'users_posts_follow';

    protected $fillable = [
        'user_id', 'post_id',
    ];
}
