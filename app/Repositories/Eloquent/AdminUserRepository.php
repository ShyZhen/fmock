<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2020/1/20
 * Time: 9:17
 */

namespace App\Repositories\Eloquent;

class AdminUserRepository extends Repository
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
        return 'App\Models\AdminUser';
    }
}
