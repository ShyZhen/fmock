<?php

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
    public $queue = 'FMock';

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
        //
        $event->user->avatar = 'test' . rand();
        $event->user->save();
    }
}
