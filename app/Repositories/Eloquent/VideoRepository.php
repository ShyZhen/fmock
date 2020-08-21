<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/25
 * Time: 15:01
 */

namespace App\Repositories\Eloquent;

class VideoRepository extends Repository
{
    /**
     * 实现抽象函数获取模型
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return string
     */
    public function model()
    {
        return 'App\Models\Video';
    }

    /**
     * 获取某个用户集合的回答
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $userIdArr
     *
     * @return mixed
     */
    public function getResourcesByUserIdArr($userIdArr)
    {
        return $this->model::with('user')
            ->select('id', 'user_id', 'uuid', 'title', 'summary', 'poster', 'collect_num', 'comment_num', 'created_at')
            ->where('deleted', 'none')
            ->whereIn('user_id', $userIdArr)
            ->orderByDesc('created_at')
            ->paginate(env('PER_PAGE', 10));
    }
}
