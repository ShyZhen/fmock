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
    // 七牛设置的图片样式（加水印等其他操作）
    // imageView2/0/h/1080/q/100|watermark/2/text/Rk1vY2suY29t/font/dGVybWluYWw=/fontsize/240/fill/I0VCRUNFNA==/dissolve/75/gravity/SouthEast/dx/10/dy/10|imageslim
    protected $imageProcess = '_fmock';

    // imageView2/1/w/175/h/140/q/100|watermark/2/text/Rk1vY2suY29t/font/dGVybWluYWw=/fontsize/240/fill/I0VCRUNFNA==/dissolve/75/gravity/SouthEast/dx/10/dy/10
    protected $imagePosterProcess = '_fmockmin';

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
            $userInfo['avatar'] = $user->avatar;
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
