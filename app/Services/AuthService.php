<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: 2018/8/22
 * Time: 20:35
 */

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;

class AuthService extends Service
{
    private $redisService;

    private $emailService;

    private $userRepository;

    public function __construct(
        RedisService $redisService,
        EmailService $emailService,
        UserRepository $userRepository
    ) {
        $this->redisService = $redisService;
        $this->emailService = $emailService;
        $this->userRepository = $userRepository;
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
            return [
                'status_code' => 400,
                'message' => __('app.email_ttl').$this->redisService->getRedisTtl('user:email:'.$account).'s'
            ];
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

    /**
     * 注册服务
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     * @param $name
     * @param $password
     * @param $email
     * @param $verifyCode
     * @return array
     */
    public function register($name, $password, $email, $verifyCode)
    {
        $code = $this->redisService->getRedis('user:email:'.$email);
        if ($code) {
            if ($code == $verifyCode) {
                $uuid = $this->uuid('user-');
                $user = $this->userRepository->create([
                    'name' => $name,
                    'password' => bcrypt($password),
                    'email' => $email,
                    'uuid' => $uuid
                ]);
                $token = $user->createToken(env('APP_NAME'))->accessToken;
                return [
                    'status_code' => 201,
                    'access_token' => $token
                ];
            } else {
                return [
                    'status_code' => 401,
                    'message' => __('app.verify_code') . __('app.error')
                ];
            }
        } else {

            return [
                'status_code' => 400,
                'message' => __('app.verify_code') . __('app.nothing_or_expire')
            ];
        }
    }

    /**
     * 登录服务
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     * @param $email
     * @param $password
     * @return array
     */
    public function login($email, $password)
    {
        $user = $this->userRepository->getFirstUserByEmail($email);
        if ($user && $user->closure == 'none') {
            if ($this->verifyPasswordLimit($email)) {
                return [
                    'status_code' => 403,
                    'message' => '您请求次数过多，请稍后重试'
                ];
            }
            if (Auth::attempt(['email' => $email, 'password' => $password])) {
                $token = $user->createToken(env('APP_NAME'))->accessToken;
                return [
                    'status_code' => 200,
                    'access_token' => $token
                ];
            } else {
                return [
                    'status_code' => 422,
                    'message' => __('app.password') . __('app.error')
                ];
            }
        } else {

            return [
                'status_code' => 0,
                'message' => '用户不存在或者已冻结'
            ];
        }
    }

    /**
     * 密码错误限制
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     * @param $email
     * @return bool
     */
    private function verifyPasswordLimit($email)
    {
        if ($this->redisService->isRedisExists('login:times:'.$email)) {
            $this->redisService->redisIncr('login:times:'.$email);
            if ($this->redisService->getRedis('login:times:'.$email) >= 10) {
                return true;
            }
        } else {
            $this->redisService->setRedis('login:times:'.$email, 1, 'EX', 600);
            return false;
        }
    }
}