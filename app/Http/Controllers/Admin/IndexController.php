<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2020/1/20
 * Time: 12:53
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Base\AdminBaseController;

class IndexController extends AdminBaseController
{
    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return string
     */
    public function index()
    {
        return 'hello admin';
    }

    /**
     * 管理员 首页
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return mixed
     */
    public function dashboard()
    {
        return view('admin.dashboard.index');
    }
}
