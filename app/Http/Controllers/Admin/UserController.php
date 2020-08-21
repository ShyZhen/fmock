<?php
/**
 * Created by PhpStorm.
 * User shyZhen <huaixiu.zhen@gmail.com>
 * Date: 2020/1/20
 * Time: 9:11
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Eloquent\UserRepository;
use App\Http\Controllers\Admin\Base\AdminBaseController;

class UserController extends AdminBaseController
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * 获取用户列表
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Request $request)
    {
        $where = $this->handlePostRequestParams($request->all());
        $users = $this->userRepository->model()
            ::where($where)
            ->paginate(env('PER_PAGE', 10));

        return response()->json([
            'code' => self::SUCCESS_CODE,
            'data' => $users,
        ]);
    }

    public function setClosure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uuid' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'code' => self::ERROR_CODE,
                    'message' => $validator->errors()->first(),
                ]
            );
        } else {
            return $this->userRepository->update(['closure' => 'yes'], $request->get('uuid'), 'uuid');
        }
    }
}
