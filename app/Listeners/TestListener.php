<?php

/**
 * 使用队列处理事件必须继承 ShouldQueue
 * 启动命令：php artisan queue:work redis --queue=FMock --daemon --quiet --delay=3 --sleep=3 --tries=3
 */

namespace App\Listeners;

use App\Events\Test;
use Illuminate\Contracts\Queue\ShouldQueue;

class TestListener implements ShouldQueue
{
    /**
     * 任务应该发送到的队列的连接的名称
     *
     * @var string|null
     */
    public $connection = 'redis';

    /**
     * 任务应该发送到的队列的名称
     *
     * @var string|null
     */
    public $queue = 'FMockTestQueue';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param Test $event
     *
     * @return void
     */
    public function handle(Test $event)
    {
        // 测试更新该用户的头像
        $event->user->avatar = 'test' . rand();
        $event->user->save();
    }
}
