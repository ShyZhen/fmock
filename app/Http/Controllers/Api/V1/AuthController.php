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
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * 发送注册验证码
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function registerCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255|unique:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                400
            );
        } else {
            $email = $request->get('email');

            return $this->authService->sendRegisterCode($email);
        }
    }

    /**
     * 注册
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:16',
            'verify_code' => 'required|size:6',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6|max:255|confirmed',
        ]);
        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                400
            );
        } else {

            return $this->authService->register(
                $request->get('name'),
                $request->get('password'),
                $request->get('email'),
                $request->get('verify_code')
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
     * @param Request $request
     * @return array
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required|min:6|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                400
            );
        } else {
            $email = $request->get('email');
            $password = $request->get('password');

            return $this->authService->login($email, $password);
        }
    }

    /**
     * 改密验证码
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function passwordCode(Request $request)
    {
        // $preg_tel = '/^1[3|4|5|8|7][0-9]\d{8}$/';
        // $preg_email = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255|exists:users,email',
        ]);
        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                400
            );
        } else {
            $email = $request->get('email');

            return $this->authService->sendPasswordCode($email);
        }
    }

    /**
     * 改密
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255|exists:users,email',
            'verify_code' => 'required|size:6',
            'password' => 'required|min:6|max:255|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                400
            );
        } else {
            $email = $request->get('email');
            $verifyCode = $request->get('verify_code');
            $password = $request->get('password');

            return $this->authService->changePassword($email, $verifyCode, $password);

        }
    }

    /**
     * 登出
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        return $this->authService->logout();
    }

    /**
     * 获取个人信息
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function myInfo()
    {
        return $this->authService->myInfo();
}
}