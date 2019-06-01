<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    //
    protected $table = 'posts';

    protected $fillable = [
        'title', 'summary', 'poster', 'content', 'user_id', 'uuid', 'type',
    ];

    /**
     * 文章预加载用户信息
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')
            ->select(['id', 'uuid', 'name', 'avatar', 'bio']);
    }

    /**
     * 最新评论 (已废弃，使用Comment模型获取数据)
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function newComments()
    {
        return $this->hasMany('App\Models\Comment', 'post_id', 'id')
            ->where('deleted', 'none')
            ->orderByDesc('created_at');
    }

    /**
     * 最热评论 (已废弃，使用Comment模型获取数据)
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hotComments()
    {
        return $this->hasMany('App\Models\Comment', 'post_id', 'id')
            ->where('deleted', 'none')
            ->orderByDesc('like_num');
    }
}
