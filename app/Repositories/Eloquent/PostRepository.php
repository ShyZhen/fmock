<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/25
 * Time: 15:01
 */

namespace App\Repositories\Eloquent;

class PostRepository extends Repository
{
    /**
     * 实现抽象函数获取模型
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     * @return string
     */
    public function model()
    {
        return 'App\Models\Post';
    }

    public function test()
    {
        return $this->model->where('uuid', '3333')->get();
    }

}