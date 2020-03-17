<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    //
    protected $table = 'videos';

    protected $fillable = [
        'uuid', 'user_id', 'title', 'summary', 'poster', 'is_free', 'is_release',
    ];

    /**
     * 视频集预加载用户信息
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
