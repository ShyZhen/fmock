<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 */

namespace App\Repositories\Eloquent;

class UsersFollowRepository extends Repository
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
        return 'App\Models\UsersFollow';
    }

    /**
     * 查询$currId关注$userId的记录
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $currId
     * @param $userId
     *
     * @return mixed
     */
    public function isFollowed($currId, $userId)
    {
        return $this->model::where([
            'master_user_id' => $userId,
            'following_user_id' => $currId,
        ])->first();
    }

    /**
     * 修改$currId关注$userId的互关状态
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $currId
     * @param $userId
     * @param $status
     *
     * @return mixed
     */
    public function updateFollowStatus($currId, $userId, $status)
    {
        return $this->model::where([
            'master_user_id' => $userId,
            'following_user_id' => $currId,
        ])->update([
            'both_status' => $status,
        ]);
    }

    /**
     * 获取某个用户的关注者
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $userId
     * @param $start
     * @param $limit
     *
     * @return mixed
     */
    public function getSomeoneFollows($userId, $start, $limit)
    {
        return $this->model::where([
            'following_user_id' => $userId,
        ])->offset($start)->limit($limit)->get();
    }

    /**
     * 获取我关注的所有用户ID
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $userId
     *
     * @return mixed
     */
    public function getAllFollowIds($userId)
    {
        return $this->model::where([
            'following_user_id' => $userId,
        ])->pluck('master_user_id');
    }

    /**
     * 获取某个用户的粉丝
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $userId
     * @param $start
     * @param $limit
     *
     * @return mixed
     */
    public function getSomeoneFans($userId, $start, $limit)
    {
        return $this->model::where([
            'master_user_id' => $userId,
        ])->offset($start)->limit($limit)->get();
    }

    /**
     * 在固定数组里查找某人的粉丝
     * 即关注了$currId
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $currId
     * @param $userFollowsIdArr
     *
     * @return mixed
     */
    public function getSomeoneFansByIdArr($currId, $userFollowsIdArr)
    {
        return $this->model::where(
            'master_user_id',
            $currId
        )->whereIn('following_user_id', $userFollowsIdArr)->get();
    }

    /**
     * 在固定的数组里查找某人的关注
     * 即$currId也关注了这些人
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $currId
     * @param $userFansIdArr
     *
     * @return mixed
     */
    public function getSomeoneFollowsByIdArr($currId, $userFansIdArr)
    {
        return $this->model::where(
            'following_user_id',
            $currId
        )->whereIn('master_user_id', $userFansIdArr)->get();
    }
}
