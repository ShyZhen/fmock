<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/21
 * Time: 13:04
 */

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    public function getLocale()
    {
        return __('app.test');
    }
}
