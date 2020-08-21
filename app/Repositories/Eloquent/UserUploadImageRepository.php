<?php
/**
 * 记录用户上传的文件
 *
 * Author huaixiu.zhen
 * http://litblc.com
 * User: litblc
 * Date: 2019/05/08
 * Time: 19:50
 */

namespace App\Repositories\Eloquent;

class UserUploadImageRepository extends Repository
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
        return 'App\Models\UserUploadImage';
    }
}
