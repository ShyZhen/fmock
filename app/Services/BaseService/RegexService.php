<?php
/**
 * 基础正则服务
 *
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: 2019/4/20
 * Time: 20:39
 */

namespace App\Services\BaseService;

use App\Services\Service;

class RegexService extends Service
{
    /**
     * 正则列表
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @var array
     */
    public static $rules = [
        'email' => '/^([a-zA-Z0-9]+[_|\-|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\-|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/',
        'mobile' => '/^1(?:3|4|5|6|7|8|9)\d{9}$/',
    ];

    /**
     * 正则匹配
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $regexRule
     * @param $value
     *
     * @return bool
     */
    public static function test($regexRule, $value)
    {
        if (!isset(self::$rules[$regexRule])) {
            return false;
        }

        $rule = self::$rules[$regexRule];

        return (bool) preg_match($rule, $value);
    }
}
