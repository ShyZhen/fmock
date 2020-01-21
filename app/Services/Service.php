<?php
/**
 * Service 父类
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/21
 * Time: 12:33
 */
namespace App\Services;

use Illuminate\Http\Request;

class Service
{
    /**
     * 生成 uuid
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param string $prefix
     *
     * @return string
     */
    protected static function uuid($prefix = '')
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8) . '-';
        $uuid .= substr($chars, 8, 4) . '-';
        $uuid .= substr($chars, 12, 4) . '-';
        $uuid .= substr($chars, 16, 4) . '-';
        $uuid .= substr($chars, 20, 12);

        return $prefix . $uuid;
    }

    /**
     * 随机6位验证码
     * Author huaixiu.zhen
     * http://litblc.com
     *
     * @return string
     */
    protected static function code()
    {
        $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_BOTH);

        return $code;
    }

    /**
     * 获取客户端真实IP
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return null|string
     */
    protected static function getClientIp()
    {
        // Request::setTrustedProxies([getenv('SERVER_ADDR')]);

        return Request()->getClientIp();
    }

    /**
     * 处理预加载用户信息
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $user
     *
     * @return mixed
     */
    protected function handleUserInfo($user)
    {
        if ($user) {
            $userInfo['uuid'] = $user->uuid;
            $userInfo['username'] = $user->name;
            $userInfo['avatar'] = $user->avatar ?: url('/static/image/defaultAvatar.jpg');
            $userInfo['bio'] = $user->bio;
        } else {
            $userInfo['uuid'] = 'user-anonymous';
            $userInfo['username'] = __('app.anonymous');
            $userInfo['avatar'] = url('/static/image/anonymousAvatar.jpg');
            $userInfo['bio'] = __('app.default_bio');
        }

        return $userInfo;
    }
}
