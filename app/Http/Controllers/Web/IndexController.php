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
use App\Repositories\Eloquent\UserRepository;

class IndexController
{
    public function index()
    {
        return 'hello web';
    }

    public function event(UserRepository $userRepository)
    {
        $user = $userRepository->find(14);
        event(new Test($user));

        return '事件测试';
    }
}
