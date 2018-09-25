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
     * 获取我关注的文章
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return mixed
     */
    public function getMyFollowedPosts()
    {
        // return $this->find(Auth::id())->myFollowedPosts();
        return Auth::user()->myFollowedPosts()
            ->orderBy('updated_at', 'desc')
            ->paginate(env('PER_PAGE', 10));
    }

    /**
     * 通过id获取用户信息，供首页文章显示
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $id
     *
     * @return mixed
     */
    public function getUserInfoById($id)
    {
        $user = $this->find($id);

        if ($user) {
            $userInfo['uuid'] = $user->uuid;
            $userInfo['username'] = $user->name;
            $userInfo['avatar'] = ($user->avatar ? url($user->avatar) : url('/static/defaultAvatar.jpg'));
            $userInfo['bio'] = $user->bio;
        } else {
            $userInfo['uuid'] = '';
            $userInfo['username'] = __('app.anonymous');
            $userInfo['avatar'] = url('/static/anonymousAvatar.jpg');
            $userInfo['bio'] = '';
        }

        return $userInfo;
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
}
