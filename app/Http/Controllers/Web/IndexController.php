<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/12/19
 * Time: 15:59
 */

namespace App\Http\Controllers\Web;

use App\Events\Test;
use App\Models\User;
use App\Notifications\TestNotifications;
use App\Repositories\Eloquent\UserRepository;

class IndexController
{
    public function index()
    {
        return 'hello web';
    }

    /**
     * 事件测试 自动加入到队列(redis) 执行完毕删除
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param UserRepository $userRepository
     *
     * @return string
     */
    public function event(UserRepository $userRepository)
    {
        $user = $userRepository->find(14);
        event(new Test($user));

        return '事件测试';
    }

    /**
     * 消息通知测试 生成消息入库(mysql)
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     */
    public function notification()
    {
        $user = User::find(14);
        if ($user) {
            $notificationData['username'] = $user->name;
            $notificationData['userId'] = $user->id;
            $notificationData['action'] = '赞了';
            $notificationData['content'] = '文章';
            dd($user->notify(new TestNotifications($notificationData)));
        }
    }

    /**
     * 消息通知测试 获取消息
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     */
    public function getNotification()
    {
        $user = User::find(14);
        if ($user) {
//            dd($user->notifications);  // 全部通知
            print_r($user->unreadNotifications);  // 未读通知
        $user->unreadNotifications->markAsRead(); // 标为已读
        }
    }
}
