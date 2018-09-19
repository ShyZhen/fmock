<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/23
 * Time: 14:17
 */
namespace App\Repositories\Eloquent;

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
}
