<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: Response::HTTP_CREATED8/8/22
 * Time: 20:35
 */

namespace App\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Eloquent\UserRepository;

class AuthService extends Service
{
    private $redisService;

    private $emailService;

    private $userRepository;

    /**
     * AuthService constructor.
     * @param RedisService $redisService
     * @param EmailService $emailService
     * @param UserRepository $userRepository
     */
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
        if ($this->redisService->isRedisExists('user:email:' . $account)) {
            return response()->json(
                ['message' => __('app.email_ttl') . $this->redisService->getRedisTtl('user:email:' . $account).'s'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } else {
            $code = $this->code();
            $data = ['data' => __('app.verify_code') . $code . __('app.email_error')];
            $subject = __('app.fmock_register_service');
            $mail = $this->emailService->sendEmail($account, $data, $subject);
            if ($mail) {
                $this->redisService->setRedis('user:email:' . $account, $code, 'EX', 600);
                return response()->json(
                    ['message' => __('app.send_email').__('app.success')],
                    Response::HTTP_OK
                );
            }

            return response()->json(
                ['message' => __('app.try_again')],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * 发送改密验证码服务 目前使用email服务
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     * @param $email
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function sendPasswordCode($email)
    {
        if ($this->redisService->getRedis('password:email:' . $email)) {
            return response()->json(
                ['message' => __('app.email_ttl') . $this->redisService->getRedisTtl('password:email:' . $email) . 's'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } else {
            $code = $this->code();
            $this->redisService->setRedis('password:email:' . $email, $code, 'EX', 600);
            $data = [
                'data' => __('app.verify_code') . $code . __('app.email_error')
            ];
            $subject = __('app.fmock_reset_pwd_service');
            $mail = $this->emailService->sendEmail($email, $data, $subject);
            if ($mail) {
                return response()->json(
                    ['message' => __('app.send_email').__('app.success')],
                    Response::HTTP_OK
                );
            }

            return response()->json(
                ['message' => __('app.try_again')],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
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
                return response()->json(
                    ['access_token' => $token],
                    Response::HTTP_CREATED
                );
            } else {
                return response()->json(
                    ['message' => __('app.verify_code') . __('app.error')],
                    Response::HTTP_UNAUTHORIZED
                );
            }
        }

        return response()->json(
            ['message' => __('app.verify_code') . __('app.nothing_or_expire')],
            Response::HTTP_UNPROCESSABLE_ENTITY
        );

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
                return response()->json(
                    ['message' => __('app.request_too_much')],
                    Response::HTTP_FORBIDDEN
                );
            }
            if (Auth::attempt(['email' => $email, 'password' => $password])) {
                $token = $user->createToken(env('APP_NAME'))->accessToken;
                return response()->json(
                    ['access_token' => $token],
                    Response::HTTP_OK
                );
            } else {
                return response()->json(
                    ['message' => __('app.password') . __('app.error')],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }

        return response()->json(
            ['message' => __('app.user_is_closure')],
            Response::HTTP_BAD_REQUEST
        );

    }

    /**
     * 改密服务
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     * @param $email
     * @param $verifyCode
     * @param $password
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword($email, $verifyCode, $password)
    {
        $code = $this->redisService->getRedis('password:email:' . $email);
        if ($code) {
            if ($code == $verifyCode) {
                $user = $this->userRepository->getFirstUserByEmail($email);
                $user->password = bcrypt($password);
                $user->save();
                return response()->json(
                    ['message' => __('app.change') . __('app.success')],
                    Response::HTTP_OK
                );
            } else {
                return response()->json(
                    ['message' => __('app.verify_code') . __('app.error')],
                    Response::HTTP_UNAUTHORIZED
                );
            }
        }

        return response()->json(
            ['message' => __('app.verify_code') . __('app.nothing_or_expire')],
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function myInfo()
    {
        return response()->json(
            ['data' => Auth::user()],
            Response::HTTP_OK
        );
    }

    /**
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::guard('api')->user()->token()->delete();

        return response()->json(
            ['message' => __('app.logout') . __('app.success')],
            Response::HTTP_OK
        );
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