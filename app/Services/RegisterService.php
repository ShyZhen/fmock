<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: 2018/8/22
 * Time: 20:35
 */

namespace App\Services;

class RegisterService extends Service
{
    private $redisService;

    private $emailService;

    public function __construct(RedisService $redisService, EmailService $emailService)
    {
        $this->redisService = $redisService;
        $this->emailService = $emailService;
    }

    /**
     * 发送注册码服务 目前使用email服务
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     * @param $account
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function sendRegisterCode($account)
    {
        if ($this->redisService->isRedisExists('user:email:'.$account)) {
            return response()->json([
                'status_code' => 400,
                'message' => __('app.email_ttl').$this->redisService->getRedisTtl('user:email:'.$account).'s'
            ]);
        } else {
            $code = $this->code();
            $data = ['data' => __('app.verify_code') . $code . __('app.email_error')];
            $subject = __('app.fmock_register_service');
            $mail = $this->emailService->sendEmail($account, $data, $subject);
            if ($mail) {
                $this->redisService->setRedis('user:email:'.$account, $code, 'EX', 600);
                return [
                    'status_code' => 200,
                    'message' => __('app.send_email').__('app.success')
                ];
            }

            return [
                'status_code' => 500,
                'message' => __('app.try_again')
            ];
        }
    }
}