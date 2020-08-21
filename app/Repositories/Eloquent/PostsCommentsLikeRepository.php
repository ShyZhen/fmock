<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 */

namespace App\Repositories\Eloquent;

use Illuminate\Support\Facades\Auth;

class PostsCommentsLikeRepository extends Repository
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
        return 'App\Models\PostsCommentsLike';
    }

    /**
     * 查找是否存在 赞/踩
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $postId
     * @param $type
     * @param $resource
     *
     * @return mixed
     */
    public function hasAction($postId, $type, $resource)
    {
        return $this->model->where([
            'user_id' => Auth::id(),
            'resource_id' => $postId,
            'action' => $type,
            'type' => $resource,
        ])->first();
    }

    /**
     * 删除一条 赞/踩 数据
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $pivotId
     *
     * @return mixed
     */
    public function deleteAction($pivotId)
    {
        return $this->delete($pivotId);
    }

    /**
     * 生成 赞/踩
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $postId
     * @param $type
     * @param $resource
     *
     * @return mixed
     */
    public function makeAction($postId, $type, $resource)
    {
        return $this->create([
            'user_id' => Auth::id(),
            'resource_id' => $postId,
            'action' => $type,
            'type' => $resource,
        ]);
    }
}
