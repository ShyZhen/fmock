<?php
/**
 * Created by PhpStorm.
 * User shyZhen <huaixiu.zhen@gmail.com>
 * Date: 2020-10-23
 * Time: 16:34
 */

namespace App\Library\RabbitMQ;

class RabbitMQHandle
{
    public function __construct()
    {
    }

    /**
     * rabbitMQ 回调函数，只写业务代码
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $msg
     */
    public function handle($msg)
    {
        print_r($msg->body . "\r\n");

        // 消费确认，保证不会丢失数据
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    }
}
