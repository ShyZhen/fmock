<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoItem extends Model
{
    //
    protected $table = 'video_items';

    protected $fillable = [
        'uuid', 'user_id', 'title', 'summary', 'poster', 'hash', 'video_key', 'url', 'hls_url', 'hls_hd_url', 'is_transcode', 'is_free', 'is_publish',
    ];

    /**
     * 视频预加载用户信息
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
