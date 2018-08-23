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
use App\Services\RegisterService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private $registerService;

    public function __construct(RegisterService $registerService)
    {
        $this->registerService = $registerService;
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
            return $this->registerService->sendRegisterCode($email);
        }
    }
}