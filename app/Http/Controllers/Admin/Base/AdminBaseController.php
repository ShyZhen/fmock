<?php
/**
 * Created by PhpStorm.
 * User shyZhen <huaixiu.zhen@gmail.com>
 * Date: 2020/1/20
 * Time: 15:06
 */

namespace App\Http\Controllers\Admin\Base;

use App\Http\Controllers\Controller;

class AdminBaseController extends Controller
{
    public const SUCCESS_CODE = 0;
    public const ERROR_CODE = -1;

    /**
     * 处理post参数 过滤csrf_token以及空值
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $request
     *
     * @return mixed
     */
    protected function handlePostRequestParams($request)
    {
        foreach ($request as $key => $value) {
            if ($key == '_token' || !$value) {
                unset($request[$key]);
            }
        }

        return $request;
    }
}
