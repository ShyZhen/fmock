<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 */

namespace App\Repositories\Eloquent;

class TimelinesFollowRepository extends Repository
{
    /**
     * 实现抽象函数获取模型
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return string
     */
    public function model()
    {
        return 'App\Models\TimelinesFollow';
    }
}
