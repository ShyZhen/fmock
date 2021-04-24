<?php

/**
 * Author huaixiu.zhen
 * http://litblc.com
 * User: litblc
 * Date: 2018/08/21
 * Time: 16:50
 */

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    use HasApiTokens;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'mobile', 'gender', 'password', 'uuid', 'github_id', 'avatar', 'qq', 'wechat', 'github', 'github_id', 'wechat_openid',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'github_id', 'wechat_openid',
    ];

    /**
     * 获取我关注收藏的文章 按关注时间排序
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function myCollectedPosts()
    {
        return $this->belongsToMany('App\Models\Post', 'posts_follow', 'user_id', 'resource_id')
//            ->withPivot('type')
//            ->wherePivot('type', 'post')    // 过滤中间表type为post的
            ->where('deleted', 'none')
            ->orderByDesc('pivot_updated_at')
            ->withTimestamps();
    }

    /**
     * 获取我关注收藏的回答 按关注时间排序
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function myCollectedAnswers()
    {
        return $this->belongsToMany('App\Models\Answer', 'answers_follow', 'user_id', 'resource_id')
//            ->withPivot('type')
//            ->wherePivot('type', 'answer')    // 过滤中间表type为answer的
            ->where('deleted', 'none')
            ->orderByDesc('pivot_updated_at')
            ->withTimestamps();
    }

    /**
     * 获取我关注收藏的视频 按关注时间排序
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function myCollectedVideos()
    {
        return $this->belongsToMany('App\Models\Video', 'videos_follow', 'user_id', 'resource_id')
            ->where('deleted', 'none')
            ->orderByDesc('pivot_updated_at')
            ->withTimestamps();
    }

    /**
     * 获取我关注收藏的文章 按关注时间排序
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function myCollectedTimelines()
    {
        return $this->belongsToMany('App\Models\Timeline', 'timelines_follow', 'user_id', 'resource_id')
//            ->withPivot('type')
//            ->wherePivot('type', 'post')    // 过滤中间表type为post的
            ->where('deleted', 'none')
            ->orderByDesc('pivot_updated_at')
            ->withTimestamps();
    }
}
