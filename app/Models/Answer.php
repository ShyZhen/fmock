<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    //
    protected $table = 'answers';

    protected $fillable = [
        'uuid', 'user_id', 'post_id', 'title', 'summary', 'poster', 'content',
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
}
