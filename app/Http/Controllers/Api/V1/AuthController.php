<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: 2018/8/22
 * Time: 20:31
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private $authService;

    /**
     * AuthController constructor.
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * 发送注册验证码
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param Request $request
     *
     * @throws \AlibabaCloud\Client\Exception\ClientException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerCode(Request $request)
    {
        $account = $request->get('account');

        // 正则验证是邮箱还是手机号
        $type = $this->authService->regexAccountType($account);

        if ($type) {
            $validator = Validator::make($request->all(), [
                'account' => 'max:255|unique:users,' . $type,
            ]);

            if ($validator->fails()) {
                return response()->json(
                    ['message' => $validator->errors()->first()],
                    Response::HTTP_BAD_REQUEST
                );
            } else {
                return $this->authService->sendRegisterCode($account, $type);
            }
        } else {
            return response()->json(
                ['message' => __('app.account_validate_fail')],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * 注册
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param Request $request
     *
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $account = $request->get('account');

        // 正则验证是邮箱还是手机号
        $type = $this->authService->regexAccountType($account);

        if ($type) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:16|unique:users,name',
                'verify_code' => 'required|size:6',
                'account' => 'max:255|unique:users,' . $type,
                'password' => 'required|min:6|max:255|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    ['message' => $validator->errors()->first()],
                    Response::HTTP_BAD_REQUEST
                );
            } else {
                return $this->authService->register(
                    $request->get('name'),
                    $request->get('password'),
                    $account,
                    $request->get('verify_code'),
                    $type
                );
            }
        } else {
            return response()->json(
                ['message' => __('app.account_validate_fail')],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * 登录
     * 所有鉴权失败都应跳转到login
     * 所有需要鉴权的操作需要在header携带登录所生成的access_token
     * headers => [
     *    'Accept' => 'application/json',
     *    'Authorization' => 'Bearer '.$accessToken,
     * ]
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param Request $request
     *
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $account = $request->get('account');

        // 正则验证是邮箱还是手机号
        $type = $this->authService->regexAccountType($account);

        if ($type) {
            $validator = Validator::make($request->all(), [
                'account' => 'max:255',
                'password' => 'required|min:6|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    ['message' => $validator->errors()->first()],
                    Response::HTTP_BAD_REQUEST
                );
            } else {
                $password = $request->get('password');

                return $this->authService->login($account, $password, $type);
            }
        } else {
            return response()->json(
                ['message' => __('app.account_validate_fail')],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * 改密验证码
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param Request $request
     *
     * @throws \AlibabaCloud\Client\Exception\ClientException
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function passwordCode(Request $request)
    {
        $account = $request->get('account');

        // 正则验证是邮箱还是手机号
        $type = $this->authService->regexAccountType($account);

        if ($type) {
            $validator = Validator::make($request->all(), [
                'account' => 'max:255|exists:users,' . $type,
            ]);

            if ($validator->fails()) {
                return response()->json(
                    ['message' => $validator->errors()->first()],
                    Response::HTTP_BAD_REQUEST
                );
            } else {
                return $this->authService->sendPasswordCode($account, $type);
            }
        } else {
            return response()->json(
                ['message' => __('app.account_validate_fail')],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * 改密
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function password(Request $request)
    {
        $account = $request->get('account');

        // 正则验证是邮箱还是手机号
        $type = $this->authService->regexAccountType($account);

        if ($type) {
            $validator = Validator::make($request->all(), [
                'account' => 'max:255|exists:users,' . $type,
                'verify_code' => 'required|size:6',
                'password' => 'required|min:6|max:255|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    ['message' => $validator->errors()->first()],
                    Response::HTTP_BAD_REQUEST
                );
            } else {
                $verifyCode = $request->get('verify_code');
                $password = $request->get('password');

                return $this->authService->changePassword($account, $verifyCode, $password, $type);
            }
        } else {
            return response()->json(
                ['message' => __('app.account_validate_fail')],
                Response::HTTP_BAD_REQUEST
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
        return $this->authService->logout();
    }

    /**
     * 获取个人信息(根据is_rename判断是否可以改昵称)
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function myInfo()
    {
        return $this->authService->myInfo();
    }

    /**
     * 更新个人信息(不包括昵称)
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function updateMyInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gender' => 'max:10|in:male,female,secrecy',
            'birthday' => 'date',
            'reside_city' => 'max:16',
            'bio' => 'max:32',
            'intro' => 'max:128',
            'company' => 'max:32',
            'company_type' => 'max:32',
            'position' => 'max:32',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->authService->updateMyInfo($request->all());
        }
    }

    /**
     * 修改用户昵称
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMyName(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:20|unique:users,name,' . Auth::id() . ',id',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->authService->updateMyName($request->get('name'));
        }
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
        return $this->authService->getUserByUuid($uuid);
    }

    /**
     * 判断当前账号状态，是否存在和冻结
     * 用于输入框缺失焦点时触发
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccountStatus(Request $request)
    {
        $account = $request->get('account');

        // 正则验证是邮箱还是手机号
        $type = $this->authService->regexAccountType($account);

        if ($type) {
            return $this->authService->getAccountStatus($account, $type);
        } else {
            return response()->json(
                ['message' => __('app.account_validate_fail')],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    // 以下为干饭组快捷登录接口

    /**
     * 快捷登录，发送普通短信验证码
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickLoginCode(Request $request): \Illuminate\Http\JsonResponse
    {
        $account = $request->get('account');

        // 正则验证是邮箱还是手机号
        $type = $this->authService->regexAccountType($account);

        if ($type) {
            return $this->authService->sendLoginCode($account, $type);
        } else {
            return response()->json(
                ['message' => __('app.account_validate_fail')],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function quickLogin(Request $request): \Illuminate\Http\JsonResponse
    {
        $account = $request->get('account');
        $code = $request->get('code');

        // 正则验证是邮箱还是手机号
        $type = $this->authService->regexAccountType($account);

        if ($type) {
            return $this->authService->quickLogin($account, $code, $type);
        } else {
            return response()->json(
                ['message' => __('app.account_validate_fail')],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
