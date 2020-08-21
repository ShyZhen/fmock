<?php
/**
 * Created by PhpStorm.
 * User shyZhen <huaixiu.zhen@gmail.com>
 * Date: 2020/1/20
 * Time: 9:11
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Eloquent\AdminUserRepository;
use App\Http\Controllers\Admin\Base\AdminBaseController;

class AuthController extends AdminBaseController
{
    private $adminUserRepository;

    /**
     * AuthController constructor.
     *
     * @param AdminUserRepository $adminUserRepository
     */
    public function __construct(AdminUserRepository $adminUserRepository)
    {
        $this->adminUserRepository = $adminUserRepository;
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function login(Request $request)
    {
        // 已登录状态直接跳到首页
        if (Auth::guard('admin')->check()) {
            return redirect('/dashboard');
        }

        // 判断请求方法
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'account' => 'required|max:255',
                'password' => 'required|min:5|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => self::ERROR_CODE,
                    'message' => $validator->errors()->first(),
                ]);
            } else {
                $userParam = ['username' => $request->get('account'), 'password' => $request->get('password')];
                if (Auth::guard('admin')->attempt($userParam)) {
                    return response()->json([
                        'code' => self::SUCCESS_CODE,
                        'message' => __('app.login') . __('app.success'),
                    ]);
                } else {
                    return response()->json([
                        'code' => self::ERROR_CODE,
                        'message' => __('app.password') . __('app.error'),
                    ]);
                }
            }
        } else {
            return view('admin.auth.login');
        }
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function logout()
    {
        Auth::guard('admin')->logout();

        return redirect('/login');
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function password(Request $request)
    {
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:5|max:255|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => self::ERROR_CODE,
                    'message' => $validator->errors()->first(),
                ]);
            } else {
                $user = $this->adminUserRepository->find(Auth::guard('admin')->id());
                $user->password = bcrypt($request->get('password'));
                if ($user->save()) {
                    Auth::guard('admin')->logout();

                    return response()->json([
                        'code' => self::SUCCESS_CODE,
                        'message' => '',
                    ]);
                } else {
                    return response()->json([
                        'code' => self::ERROR_CODE,
                        'message' => __('app.try_again'),
                    ]);
                }
            }
        } else {
            return view('admin.auth.password');
        }
    }
}
