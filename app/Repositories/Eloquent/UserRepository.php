<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/23
 * Time: 14:17
 */
namespace App\Repositories\Eloquent;

use Illuminate\Support\Facades\Auth;

class UserRepository extends Repository
{
    /**
     * 实现抽象函数获取模型
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return mixed|string
     */
    public function model()
    {
        return 'App\Models\User';
    }

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $email
     *
     * @return mixed
     */
    public function getFirstUserByEmail($email)
    {
        return $this->findBy('email', $email);
    }

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $mobile
     *
     * @return mixed
     */
    public function getFirstUserByMobile($mobile)
    {
        return $this->findBy('mobile', $mobile);
    }

    /**
     * 获取我关注的文章
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return mixed
     */
    public function getMyFollowedPosts()
    {
        return Auth::user()->myFollowedPosts()
            ->paginate(env('PER_PAGE', 10));
    }

    /**
     * 同步中间表 更新用户关注文章的数据
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $postId
     *
     * @return mixed
     */
    public function followPost($postId)
    {
        return Auth::user()->myFollowedPosts()->syncWithoutDetaching($postId);
    }

    /**
     * 取消关注
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $postId
     *
     * @return mixed
     */
    public function unFollow($postId)
    {
        return Auth::user()->myFollowedPosts()->detach($postId);
    }
}
