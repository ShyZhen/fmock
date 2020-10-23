<?php
/**
 * Created by PhpStorm.
 * User shyZhen <huaixiu.zhen@gmail.com>
 * Date: 2020-10-22
 * Time: 15:43
 */

namespace App\Library\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class Consume
{
    private $mq;
    private $channel;
    private $config;

    public function __construct()
    {
        if (!$this->mq) {
            $this->config = config('queue.connections.rabbitmq.hosts');

            $this->mq = new AMQPStreamConnection(
                $this->config['host'],
                $this->config['port'],
                $this->config['user'],
                $this->config['password']
            );
        }

        $this->channel = $this->mq->channel();
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $queueName
     * @param $callback
     *
     * @throws \ErrorException
     */
    public function consume($queueName, array $callback)
    {
        $this->channel->queue_declare($queueName, true, true, false, false);

        // no_ask参数规定必须消费确认
        $this->channel->basic_consume($queueName, '', false, false, false, false, $callback);

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    /**
     * 回调方法移植到 RabbitMQHandle
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $msg
     */
    public function mpCallback($msg)
    {
        echo 'callback：', $msg->body, "\n";

        // 消费确认，保证不会丢失数据
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param array $data
     *
     * @return array
     */
    public function success($data = [])
    {
        return [
            'code' => 1,
            'data' => $data,
        ];
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param string $message
     *
     * @return array
     */
    public function error($message = '')
    {
        return [
            'code' => 0,
            'message' => $message,
        ];
    }

    /**
     * @throws \Exception
     */
    public function __destruct()
    {
        $this->channel && $this->channel->close();
        $this->mq->close();
    }
}
