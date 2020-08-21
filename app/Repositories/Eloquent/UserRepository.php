<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/23
 * Time: 14:17
 */

namespace App\Repositories\Eloquent;

use Illuminate\Support\Facades\Auth;

class UserRepository extends Repository
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
        return 'App\Models\User';
    }

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $email
     *
     * @return mixed
     */
    public function getFirstUserByEmail($email)
    {
        return $this->findBy('email', $email);
    }

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $mobile
     *
     * @return mixed
     */
    public function getFirstUserByMobile($mobile)
    {
        return $this->findBy('mobile', $mobile);
    }

    /**
     * 获取我关注的文章/回答
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $type
     *
     * @return mixed
     */
    public function getMyCollected($type)
    {
        // myCollectedPosts() or myCollectedAnswers() 方法
        $func = 'myCollected' . ucfirst($type) . 's';

        return Auth::user()->$func()
            ->paginate(env('PER_PAGE', 10));
    }

    /**
     * 同步中间表 更新用户关注文章、回答的数据
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $postId
     * @param $type
     *
     * @return mixed
     */
    public function collect($postId, $type)
    {
        // sync 方法会删掉其他；attach方法会增加相同的；
        // 而syncWithoutDetaching 也就是sync(a, false)不会删掉其他，也不会增加相同的

        // myCollectedPosts or myCollectedAnswers
        $func = 'myCollected' . ucfirst($type) . 's';

        return Auth::user()->$func()->syncWithoutDetaching($postId);
//        return Auth::user()->$func()->syncWithoutDetaching([$postId => ['type' => $type]]);
    }

    /**
     * 同步中间表 更新用户关注文章的数据
     * (已废弃) 使用 follow() 统一方法
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $postId
     *
     * @return mixed
     */
//    public function followAnswer($postId)
//    {
//        return Auth::user()->myFollowedAnswers()->syncWithoutDetaching([$postId => ['type' => 'answer']]);
//    }

    /**
     * 取消关注
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $postId
     * @param $type
     *
     * @return mixed
     */
    public function unCollect($postId, $type)
    {
        // myFollowedPosts or myFollowedAnswers
        $func = 'myCollected' . ucfirst($type) . 's';

        return Auth::user()->$func()->detach($postId);
    }

    /**
     * 取消关注
     * （已废弃）使用 unFollow() 统一方法
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $postId
     *
     * @return mixed
     */
//    public function unFollowAnswer($postId)
//    {
//        return Auth::user()->myFollowedAnswers()->detach($postId);
//    }

    /**
     * 根据用户ID集合 查询用户信息
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $idArr
     *
     * @return mixed
     */
    public function getUsersByIdArr($idArr)
    {
        return $this->model::whereIn('id', $idArr)->get(['id', 'uuid', 'name', 'avatar', 'bio']);
    }
}
