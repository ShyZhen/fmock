<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/12/03
 * Time: 15:01
 */

namespace App\Repositories\Eloquent;

class CommentRepository extends Repository
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
        return 'App\Models\Comment';
    }

    /**
     * 获取最新评论
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $postId
     * @param $type
     *
     * @return mixed
     */
    public function getAllNewComments($postId, $type)
    {
        $comments = $this->model::with('user')
            ->where(['resource_id' => $postId, 'type' => $type])
            ->orderByDesc('created_at')
            ->paginate(env('PER_PAGE', 10));

        return $comments;
    }

    /**
     * 获取最热评论
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $postId
     * @param $type
     *
     * @return mixed
     */
    public function getAllHotComments($postId, $type)
    {
        $comments = $this->model::with('user')
            ->where(['resource_id' => $postId, 'type' => $type])
            ->orderByDesc('like_num')
            ->paginate(env('PER_PAGE', 10));

        return $comments;
    }

    /**
     * 获取父级评论信息
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $parentId
     *
     * @return mixed
     */
    public function getParentComment($parentId)
    {
        $parentComment = $this->model::with('user')
            ->find($parentId);

        return $parentComment;
    }

    /**
     * 获取某个用户发布过的热评
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $userId
     *
     * @return mixed
     */
    public function getCommentsByUserId($userId)
    {
        $comments = $this->model::with('user')
            ->where(['user_id' => $userId])
            ->orderByDesc('created_at')
            ->paginate(env('PER_PAGE', 10));

        return $comments;
    }
}
