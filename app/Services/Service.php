<?php
/**
 * Service 父类.
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/21
 * Time: 12:33
 */

namespace App\Services;

class Service
{
    /**
     * 生成 uuid.
     *
     * Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param string $prefix
     *
     * @return string
     */
    protected function uuid($prefix = '')
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8).'-';
        $uuid .= substr($chars, 8, 4).'-';
        $uuid .= substr($chars, 12, 4).'-';
        $uuid .= substr($chars, 16, 4).'-';
        $uuid .= substr($chars, 20, 12);

        return $prefix.$uuid;
    }

    /**
     * 随机6位验证码
     * Author huaixiu.zhen
     * http://litblc.com.
     *
     * @return string
     */
    protected function code()
    {
        $code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_BOTH);

        return $code;
    }
}
