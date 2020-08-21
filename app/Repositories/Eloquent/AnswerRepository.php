<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2019/8/26
 * Time: 11:29
 */

namespace App\Repositories\Eloquent;

class AnswerRepository extends Repository
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
        return 'App\Models\Answer';
    }

    /**
     * 按时间排序
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $postId
     *
     * @return mixed
     */
    public function getNewAnswer($postId)
    {
        return $this->model::with('user')
            ->select('id', 'user_id', 'uuid', 'title', 'summary', 'poster', 'collect_num', 'comment_num', 'like_num', 'dislike_num', 'created_at')
            ->where('post_id', $postId)
            ->where('deleted', 'none')
            ->orderByDesc('created_at')
            ->paginate(env('PER_PAGE', 10));
    }

    /**
     * 热门
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $postId
     * @param $limitDate
     *
     * @return mixed
     */
    public function getFavoriteAnswer($postId, $limitDate)
    {
        return $this->model::with('user')
            ->select('id', 'user_id', 'uuid', 'title', 'summary', 'poster', 'collect_num', 'comment_num', 'like_num', 'dislike_num', 'created_at')
            ->where('post_id', $postId)
            ->where('deleted', 'none')
            ->where('created_at', '>=', $limitDate)
            ->orderByDesc('like_num')
            ->paginate(env('PER_PAGE', 10));
    }

    /**
     * 获取某个用户的所有回答列表
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $userId
     *
     * @return mixed
     */
    public function getAnswersByUserId($userId)
    {
        return $this->model::with('user')
            ->select('id', 'user_id', 'uuid', 'title', 'summary', 'poster', 'collect_num', 'comment_num', 'like_num', 'dislike_num', 'created_at')
            ->where('deleted', 'none')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate(env('PER_PAGE', 10));
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
            ->select('id', 'user_id', 'uuid', 'title', 'summary', 'poster', 'collect_num', 'comment_num', 'like_num', 'dislike_num', 'created_at')
            ->where('deleted', 'none')
            ->whereIn('user_id', $userIdArr)
            ->orderByDesc('created_at')
            ->paginate(env('PER_PAGE', 10));
    }
}
