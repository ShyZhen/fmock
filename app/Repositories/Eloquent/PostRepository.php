<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/25
 * Time: 15:01
 */
namespace App\Repositories\Eloquent;

class PostRepository extends Repository
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
        return 'App\Models\Post';
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
        return $this->model::with('user')->where('deleted', 'none')
            ->orderBy('created_at', 'desc')
            ->paginate(env('PER_PAGE', 10));
    }

    /**
     * 按热度排序
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @return mixed
     */
    public function getFavoritePost()
    {
        return $this->model::with('user')->where('deleted', 'none')
            ->orderBy('like_num', 'desc')
            ->paginate(env('PER_PAGE', 10));
    }

    /**
     * 匿名文章
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return mixed
     */
    public function getAnonymousPost()
    {
        return $this->model::with('user')->where('deleted', 'none')
            ->where('user_id', 0)
            ->orderBy('created_at', 'desc')
            ->paginate(env('PER_PAGE', 10));
    }

    /**
     * 处理预加载用户信息
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     * @param $user
     * @return mixed
     */
    public function handleUserInfo($user)
    {
        if ($user) {
            $userInfo['uuid'] = $user->uuid;
            $userInfo['username'] = $user->name;
            $userInfo['avatar'] = ($user->avatar ? url($user->avatar) : url('/static/defaultAvatar.jpg'));
            $userInfo['bio'] = $user->bio;
        } else {
            $userInfo['uuid'] = 'user-anonymous';
            $userInfo['username'] = __('app.anonymous');
            $userInfo['avatar'] = url('/static/anonymousAvatar.jpg');
            $userInfo['bio'] = __('app.default_bio');
        }

        return $userInfo;
    }
}
