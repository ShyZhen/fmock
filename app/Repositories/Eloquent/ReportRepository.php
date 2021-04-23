<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2021/4/23
 * Time: 15:01
 */

namespace App\Repositories\Eloquent;

class ReportRepository extends Repository
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
        return 'App\Models\Report';
    }

    public function hasReport($userId, $resourceId, $type)
    {
        return $this->model::where('user_id', $userId)
            ->where('resource_id', $resourceId)
            ->where('type', $type)
            ->get();
    }
}
