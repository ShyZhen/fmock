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
    public function getMyFollowed($type)
    {
        // myFollowedPosts() or myFollowedAnswers() 方法
        $func = 'myFollowed' . ucfirst($type) . 's';

        return Auth::user()->$func()
            ->paginate(env('PER_PAGE', 10));
    }

    /**
     * 获取我关注收藏的回答
     * （已废弃）使用 getMyFollowed() 统一方法
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @return mixed
     */
//    public function getMyFollowedAnswers()
//    {
//        return Auth::user()->myFollowedAnswers()
//            ->paginate(env('PER_PAGE', 10));
//    }

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
    public function follow($postId, $type)
    {
        // sync 方法会删掉其他；attach方法会增加相同的；
        // 而syncWithoutDetaching 也就是sync(a, false)不会删掉其他，也不会增加相同的

        // myFollowedPosts or myFollowedAnswers
        $func = 'myFollowed' . ucfirst($type) . 's';

        return Auth::user()->$func()->syncWithoutDetaching([$postId => ['type' => $type]]);
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
    public function unFollow($postId, $type)
    {
        // myFollowedPosts or myFollowedAnswers
        $func = 'myFollowed' . ucfirst($type) . 's';

        return Auth::user()->$func()->detach($postId);
    }

    /*
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
}
