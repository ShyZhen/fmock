<?php

/**
 * Author huaixiu.zhen
 * http://litblc.com
 * User: litblc
 * Date: 2018/08/21
 * Time: 15:10
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comments';

    protected $fillable = [
        'type', 'resource_id', 'resource_uuid', 'parent_id', 'user_id', 'content', 'like_num', 'dislike_num', 'deleted',
    ];

    /**
     * 评论预加载用户信息
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
}
