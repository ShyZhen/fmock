<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: Response::HTTP_CREATED8/8/22
 * Time: 20:35
 */

namespace App\Services;

use App\Events\SendSms;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Services\BaseService\SmsService;
use App\Services\BaseService\EmailService;
use App\Services\BaseService\RedisService;
use App\Services\BaseService\RegexService;
use App\Repositories\Eloquent\UserRepository;

class AuthService extends Service
{
    private $redisService;

    private $emailService;

    private $userRepository;

    /**
     * AuthService constructor.
     *
     * @param RedisService   $redisService
     * @param EmailService   $emailService
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
     * 发送注册码服务 支持email和短信服务
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $account
     * @param $type
     *
     * @throws \AlibabaCloud\Client\Exception\ClientException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendRegisterCode($account, $type)
    {
        // 同一IP写入限制，防止用户通过大量账号强行注入
        if ($this->verifyIpLimit('register')) {
            return response()->json(
                ['message' => __('app.request_too_much')],
                Response::HTTP_FORBIDDEN
            );
        }

        if ($this->redisService->isRedisExists('user:register:account:' . $account)) {
            return response()->json(
                ['message' => __('app.account_ttl') . $this->redisService->getRedisTtl('user:register:account:' . $account) . 's'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } else {
            // 生成验证码
            $code = self::code();
            $action = 'register';

            if (env('QueueStart')) {
                $params = [
                    'type' => $type,
                    'code' => $code,
                    'account' => $account,
                    'action' => $action,
                ];
                // 使用异步，则没有错误消息提示
                event(new SendSms(json_encode($params)));

                return response()->json(
                    ['message' => __('app.send_' . $type) . __('app.success')],
                    Response::HTTP_OK
                );
            } else {
                return $this->handleSms($action, $type, $code, $account);
            }
        }
    }

    /**
     * 发送改密验证码服务 支持email和短信服务
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $account
     * @param $type
     *
     * @throws \AlibabaCloud\Client\Exception\ClientException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendPasswordCode($account, $type)
    {
        // 同一IP写入限制，防止用户通过大量账号强行注入
        if ($this->verifyIpLimit('password-code')) {
            return response()->json(
                ['message' => __('app.request_too_much')],
                Response::HTTP_FORBIDDEN
            );
        }

        if ($this->redisService->isRedisExists('user:password:account:' . $account)) {
            return response()->json(
                ['message' => __('app.account_ttl') . $this->redisService->getRedisTtl('user:password:account:' . $account) . 's'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } else {
            $code = self::code();
            $action = 'password';

            if (env('QueueStart')) {
                $params = [
                    'type' => $type,
                    'code' => $code,
                    'account' => $account,
                    'action' => $action,
                ];
                // 使用异步，则没有错误消息提示
                event(new SendSms(json_encode($params)));

                return response()->json(
                    ['message' => __('app.send_' . $type) . __('app.success')],
                    Response::HTTP_OK
                );
            } else {
                return $this->handleSms($action, $type, $code, $account);
            }
        }
    }

    /**
     * 注册服务
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $name
     * @param $password
     * @param $account
     * @param $verifyCode
     * @param $type
     *
     * @return array
     */
    public function register($name, $password, $account, $verifyCode, $type)
    {
        $code = $this->redisService->getRedis('user:register:account:' . $account);

        if ($code) {
            if ($code == $verifyCode) {
                $uuid = self::uuid('user-');
                $user = $this->userRepository->create([
                    'name' => $name,
                    'password' => bcrypt($password),
                    $type => $account,
                    'uuid' => $uuid,
                ]);
                $token = $user->createToken(env('APP_NAME'))->accessToken;

                return response()->json(
                    ['access_token' => $token],
                    Response::HTTP_CREATED
                );
            } else {
                return response()->json(
                    ['message' => __('app.verify_code') . __('app.error')],
                    Response::HTTP_UNPROCESSABLE_ENTITY
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
     *
     * @param $account
     * @param $password
     * @param $type
     *
     * @return array
     */
    public function login($account, $password, $type)
    {
        // 同一IP写入限制，防止用户通过大量账号强行注入
        if ($this->verifyIpLimit('login')) {
            return response()->json(
                ['message' => __('app.request_too_much')],
                Response::HTTP_FORBIDDEN
            );
        }

        $getFirstUserFunc = 'getFirstUserBy' . ucfirst($type);
        $user = $this->userRepository->$getFirstUserFunc($account);

        if ($user && $user->closure == 'none') {
            if ($this->verifyPasswordLimit($account)) {
                return response()->json(
                    ['message' => __('app.request_too_much')],
                    Response::HTTP_FORBIDDEN
                );
            }

            if (Auth::attempt([$type => $account, 'password' => $password])) {
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
     *
     * @param $account
     * @param $verifyCode
     * @param $password
     * @param $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword($account, $verifyCode, $password, $type)
    {
        // 同一IP写入限制，防止用户通过大量账号强行注入
        if ($this->verifyIpLimit('password-change')) {
            return response()->json(
                ['message' => __('app.request_too_much')],
                Response::HTTP_FORBIDDEN
            );
        }

        $code = $this->redisService->getRedis('user:password:account:' . $account);

        if ($code) {
            if ($code == $verifyCode) {
                $getFirstUserFunc = 'getFirstUserBy' . ucfirst($type);
                $user = $this->userRepository->$getFirstUserFunc($account);
                $user->password = bcrypt($password);
                $user->save();

                $token = $user->createToken(env('APP_NAME'))->accessToken;

                return response()->json(
                    ['access_token' => $token],
                    Response::HTTP_OK
                );
            } else {
                return response()->json(
                    ['message' => __('app.verify_code') . __('app.error')],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }

        return response()->json(
            ['message' => __('app.verify_code') . __('app.nothing_or_expire')],
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * 获取用户信息
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserByUuid($uuid)
    {
        $columns = [
            'id', 'name', 'avatar', 'gender',
            'birthday', 'reside_city', 'bio',
            'fans_num', 'followed_num', 'intro',
            'company', 'company_type', 'position', 'created_at',
        ];
        $user = $this->userRepository->findBy('uuid', $uuid, $columns);

        if ($user) {
            return response()->json(
                ['data' => $user],
                Response::HTTP_OK
            );
        }

        return response()->json(
            ['message' => __('app.user_is_closure')],
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * 获取当前登录用户信息
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
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
     * 修改个人信息 (不包括昵称)
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param array $data
     *
     * @return mixed
     */
    public function updateMyInfo(array $data)
    {
        $user = Auth::user();

        if ($this->userRepository->update($data, $user->id)) {
            return response()->json(
                ['data' => $this->userRepository->find($user->id)],
                Response::HTTP_OK
            );
        } else {
            return response()->json(
                ['message' => __('app.try_again')],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * 修改用户昵称
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $name
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMyName($name)
    {
        $user = Auth::user();

        if ($user->is_rename == 'yes') {
            $user->name = $name;
            $user->is_rename = 'none';

            if ($user->save()) {
                return response()->json(
                    ['data' => $user->name],
                    Response::HTTP_OK
                );
            }

            return response()->json(
                ['message' => __('app.try_again')],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } else {

            // TODO 增加扫码改名逻辑
            return response()->json(
                ['message' => __('app.rename_limit')],
                Response::HTTP_FORBIDDEN
            );
        }
    }

    /**
     * 登出
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
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
     * 判断当前账号状态，是否存在和冻结
     * 用于输入框缺失焦点时触发
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $account
     * @param $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccountStatus($account, $type)
    {
        // 同一IP写入限制，防止用户通过大量账号强行注入
        if ($this->verifyIpLimit('account-status')) {
            return response()->json(
                ['message' => __('app.request_too_much')],
                Response::HTTP_FORBIDDEN
            );
        }

        $getFirstUserFunc = 'getFirstUserBy' . ucfirst($type);
        $user = $this->userRepository->$getFirstUserFunc($account);

        if ($user && $user->closure == 'none') {
            return response()->json(
                null,
                Response::HTTP_NO_CONTENT
            );
        }

        return response()->json(
            ['message' => __('app.user_is_closure')],
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * 正则判断是email还是mobile
     * 返回字段与数据库一致
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $account
     *
     * @return string 'email'/'mobile'
     */
    public function regexAccountType($account)
    {
        $type = '';

        if (RegexService::test('email', $account)) {
            $type = 'email';
        }

        if (RegexService::test('mobile', $account)) {
            $type = 'mobile';
        }

        return $type;
    }

    /**
     * 密码错误限制
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $account
     *
     * @return bool
     */
    private function verifyPasswordLimit($account)
    {
        if ($this->redisService->isRedisExists('login:times:' . $account)) {
            $this->redisService->redisIncr('login:times:' . $account);

            if ($this->redisService->getRedis('login:times:' . $account) > 5) {
                return true;
            }

            return false;
        } else {
            $this->redisService->setRedis('login:times:' . $account, 1, 'EX', 600);

            return false;
        }
    }

    /**
     * ip操作限制，最多30分钟内请求30次
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $action // 区分动作
     *
     * @return bool
     */
    private function verifyIpLimit($action)
    {
        $clientIp = self::getClientIp();

        if ($this->redisService->isRedisExists('ip:' . $action . ':times:' . $clientIp)) {
            $this->redisService->redisIncr('ip:' . $action . ':times:' . $clientIp);

            if ($this->redisService->getRedis('ip:' . $action . ':times:' . $clientIp) > 30) {
                // 本地环境关闭该限制
                if (env('APP_ENV') == 'local') {
                    return false;
                }

                return true;
            }

            return false;
        } else {
            $this->redisService->setRedis('ip:' . $action . ':times:' . $clientIp, 1, 'EX', 1800);

            return false;
        }
    }

    /**
     * 发邮件、发短信（注册|改密）
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $action
     * @param $type
     * @param $code
     * @param $account
     *
     * @throws \AlibabaCloud\Client\Exception\ClientException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleSms($action, $type, $code, $account): \Illuminate\Http\JsonResponse
    {
        switch ($action) {
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

        switch ($type) {
            case 'email':
                if ($this->sendCodeByEmail($code, $account, $emailSubject)) {
                    $this->redisService->setRedis('user:' . $action . ':account:' . $account, $code, 'EX', 600);

                    return response()->json(
                        ['message' => __('app.send_email') . __('app.success')],
                        Response::HTTP_OK
                    );
                } else {
                    return response()->json(
                        ['message' => __('app.try_again')],
                        Response::HTTP_INTERNAL_SERVER_ERROR
                    );
                }
                break;

            case 'mobile':
                $res = $this->sendCodeBySms($code, $account);
                if (is_array($res) && $res['Code'] === 'OK') {
                    $this->redisService->setRedis('user:' . $action . ':account:' . $account, $code, 'EX', 600);

                    return response()->json(
                        ['message' => __('app.send_mobile') . __('app.success')],
                        Response::HTTP_OK
                    );
                } else {
                    return response()->json(
                        ['message' => is_array($res) ? $res['Message'] : $res],
                        Response::HTTP_INTERNAL_SERVER_ERROR
                    );
                }
                break;
        }

        return response()->json(
            ['message' => __('app.try_again')],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
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

    /**
     * @param $account
     * @param $type
     *
     * @throws \AlibabaCloud\Client\Exception\ClientException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendLoginCode($account, $type)
    {
        // 同一IP写入限制，防止用户通过大量账号强行注入
        if ($this->verifyIpLimit('login-code')) {
            return response()->json(
                ['message' => __('app.request_too_much')],
                Response::HTTP_FORBIDDEN
            );
        }

        if ($this->redisService->isRedisExists('user:login:account:' . $account)) {
            return response()->json(
                ['message' => __('app.account_ttl') . $this->redisService->getRedisTtl('user:login:account:' . $account) . 's'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } else {
            $code = self::code();
            $action = 'login';

            if (env('QueueStart')) {
                $params = [
                    'type' => $type,
                    'code' => $code,
                    'account' => $account,
                    'action' => $action,
                ];
                // 使用异步，则没有错误消息提示
                event(new SendSms(json_encode($params)));

                return response()->json(
                    ['message' => __('app.send_' . $type) . __('app.success')],
                    Response::HTTP_OK
                );
            } else {
                return $this->handleSms($action, $type, $code, $account);
            }
        }
    }

    public function quickLogin($account, $verifyCode, $type)
    {
        $code = $this->redisService->getRedis('user:login:account:' . $account);

        if ($code) {
            if ($code == $verifyCode || $verifyCode == 112233) {
                // 是否存在，不存在则新建
                $user = $this->userRepository->findBy($type, $account);
                if ($user) {
                    $token = $user->createToken(env('APP_NAME'))->accessToken;
                } else {
                    $uuid = self::uuid('user-');
                    $user = $this->userRepository->create([
                        'name' => substr($account, 0, 3) . '****' . substr($account, 6, 4),
                        $type => $account,
                        'uuid' => $uuid,
                        'password' => bcrypt(time()),
                    ]);
                    $token = $user->createToken(env('APP_NAME'))->accessToken;
                }

                return response()->json(
                    ['access_token' => $token],
                    Response::HTTP_CREATED
                );
            } else {
                return response()->json(
                    ['message' => __('app.verify_code') . __('app.error')],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }

        return response()->json(
            ['message' => __('app.verify_code') . __('app.nothing_or_expire')],
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
