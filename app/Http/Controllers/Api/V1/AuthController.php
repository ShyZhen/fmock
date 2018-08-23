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
            return [
                'status_code' => 400,
                'message' => $validator->errors()->first()
            ];
        } else {
            $email = $request->get('email');

            return $this->authService->sendRegisterCode($email);
        }
    }

    /**
     * 注册用户
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
            return [
                'status_code' => 400,
                'message' => $validator->errors()->first()
            ];
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
            return [
                'status_code' => 400,
                'message' => $validator->errors()->first()
            ];
        } else {
            $email = $request->get('email');
            $password = $request->get('password');

            return $this->authService->login($email, $password);
        }
    }
}