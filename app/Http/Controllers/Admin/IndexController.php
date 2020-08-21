<?php
/**
 * 首页的一些视图渲染
 *
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

    public function users()
    {
        return view('admin.users.index');
    }

    public function posts()
    {
        return view('admin.posts.index');
    }

    public function videos()
    {
        return view('admin.videos.index');
    }

    public function orders()
    {
        return view('admin.orders.index');
    }
}
