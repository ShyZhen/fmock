<?php

namespace App\Listeners;

use App\Events\SendSms;
use App\Services\BaseService\SmsService;
use App\Services\BaseService\EmailService;
use App\Services\BaseService\RedisService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSmsListener implements ShouldQueue
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
    public $queue = 'sendSmsQueue';

    private $redisService;
    private $emailService;

    /**
     * Create the event listener.
     *
     * @param RedisService $redisService
     * @param EmailService $emailService
     *
     * @return void
     */
    public function __construct(RedisService $redisService, EmailService $emailService)
    {
        //
        $this->redisService = $redisService;
        $this->emailService = $emailService;
    }

    /**
     * Handle the event.
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param SendSms $event
     *
     * @throws \AlibabaCloud\Client\Exception\ClientException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(SendSms $event)
    {
        //
        switch ($event->action) {
            case 'register':
                $emailSubject = __('app.fmock_register_service');
                break;
            case 'password':
                $emailSubject = __('app.fmock_reset_pwd_service');
                break;
            default:
                $emailSubject = '';
                break;
        }

        switch ($event->type) {
            case 'email':
                if ($this->sendCodeByEmail($event->code, $event->account, $emailSubject)) {
                    $this->redisService->setRedis('user:' . $event->action . ':account:' . $event->account, $event->code, 'EX', 600);
                }
                break;

            case 'mobile':
                $res = $this->sendCodeBySms($event->code, $event->account);
                if (is_array($res) && $res['Code'] === 'OK') {
                    $this->redisService->setRedis('user:' . $event->action . ':account:' . $event->account, $event->code, 'EX', 600);
                }
                break;
        }
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $code
     * @param $account
     * @param $subject
     *
     * @return bool
     */
    private function sendCodeByEmail($code, $account, $subject)
    {
        $data = ['data' => __('app.verify_code') . $code . __('app.email_error')];

        return $this->emailService->sendEmail($account, $data, $subject);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $code
     * @param $account
     *
     * @throws \AlibabaCloud\Client\Exception\ClientException
     *
     * @return array
     */
    private function sendCodeBySms($code, $account)
    {
        $data = ['code' => $code];

        return SmsService::sendSms($account, json_encode($data), 'FMock');
    }
}
