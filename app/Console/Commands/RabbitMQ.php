<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\RabbitMQ\Consume;
use App\Library\RabbitMQ\RabbitMQHandle;

class RabbitMQ extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'RabbitMQ Consume Start';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @throws \ErrorException
     */
    public function handle()
    {
        $rabbitMQ = new Consume();
        $rabbitMQHandle = new RabbitMQHandle();
        $rabbitMQ->consume(env('RABBITMQ_QUEUE'), [$rabbitMQHandle, 'handle']);
    }
}
