<?php

/**
 * Author huaixiu.zhen
 * http://litblc.com
 * User: litblc
 * Date: 2018/08/21
 * Time: 16:50
 */
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'uuid',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function myFollowedPosts()
    {
        return $this->belongsToMany('App\Models\Post', 'users_posts_follow', 'user_id', 'post_id')->withTimestamps();
    }
}
