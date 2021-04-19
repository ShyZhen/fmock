<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/25
 * Time: 15:01
 */

namespace App\Repositories\Eloquent;

class TimelineRepository extends Repository
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
        return 'App\Models\Timeline';
    }

    /**
     * 按时间排序
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @return mixed
     */
    public function getNewPost()
    {
        return $this->model::with('user')
            ->select('id', 'user_id', 'uuid', 'title', 'deleted', 'poster_list', 'collect_num', 'comment_num', 'like_num', 'dislike_num', 'created_at')
            ->where('deleted', 'none')
            ->orderByDesc('created_at')
            ->paginate(env('PER_PAGE', 10));
    }

    /**
     * 按热度排序
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $limitDate
     *
     * @return mixed
     */
    public function getFavoritePost($limitDate)
    {
        return $this->model::with('user')
            ->select('id', 'user_id', 'uuid', 'title', 'deleted', 'poster_list', 'collect_num', 'comment_num', 'like_num', 'dislike_num', 'created_at')
            ->where('deleted', 'none')
            ->where('created_at', '>=', $limitDate)
            ->orderByDesc('like_num')
            ->paginate(env('PER_PAGE', 10));
    }

    /**
     * 获取某个用户的所有文章列表
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $userId
     *
     * @return mixed
     */
    public function getPostsByUserId($userId)
    {
        return $this->model::with('user')
            ->select('id', 'user_id', 'uuid', 'title', 'deleted', 'poster_list', 'collect_num', 'comment_num', 'like_num', 'dislike_num', 'created_at')
            ->where('deleted', 'none')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate(env('PER_PAGE', 10));
    }

    /**
     * 获取某个用户集合的文章
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
            ->select('id', 'user_id', 'uuid', 'title', 'deleted', 'poster_list', 'collect_num', 'comment_num', 'like_num', 'dislike_num', 'created_at')
            ->where('deleted', 'none')
            ->whereIn('user_id', $userIdArr)
            ->orderByDesc('created_at')
            ->paginate(env('PER_PAGE', 10));
    }
}
