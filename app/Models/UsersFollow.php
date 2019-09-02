<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersFollow extends Model
{
    // 关注、粉丝关系表
    protected $table = 'users_follow';

    protected $fillable = [
        'master_user_id', 'following_user_id', 'both_status',
    ];
}
