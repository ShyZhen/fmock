<?php
/**
 * Created by PhpStorm.
 * User shyZhen <huaixiu.zhen@gmail.com>
 * Date: 2020-10-22
 * Time: 15:43
 */

namespace App\Library\RabbitMQ;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Publish
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
     * 发送消息进入队列
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param string $exchange
     * @param string $queueName
     * @param string $msgJson
     * @param string $type
     *
     * @return array
     */
    public function send($queueName, string $msgJson, $type = 'direct', $exchange = 'fmock-exchange')
    {
        try {
            // 声明交换机
            $this->channel->exchange_declare($exchange, $type, false, true, false);

            // 声明队列 第三个参数声明队列(持久化)
            $this->channel->queue_declare($queueName, false, true, false, false);

            // 绑定队列和交换机，用队列名作routingKey
            $this->channel->queue_bind($queueName, $exchange, $queueName);

            $msg = new AMQPMessage($msgJson);
            $this->channel->basic_publish($msg, $exchange, $queueName);
        } catch (\Exception $exception) {
            // throw new \Exception($exception->getMessage());
            return $this->error($exception->getMessage());
        }

        return $this->success();
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
