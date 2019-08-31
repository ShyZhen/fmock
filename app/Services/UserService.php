<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 */
namespace App\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Services\BaseService\RedisService;
use App\Repositories\Eloquent\UserRepository;

class UserService extends Service
{
    private $pageSize = 10;

    /**
     * 用户的最大关注上限
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @var int
     */
    private $maxFollowNum = 500;

    private $redisService;

    private $userRepository;

    /**
     * ActionService constructor.
     *
     * @param RedisService   $redisService
     * @param UserRepository $userRepository
     */
    public function __construct(
        RedisService $redisService,
        UserRepository $userRepository
    ) {
        $this->redisService = $redisService;
        $this->userRepository = $userRepository;
    }

    /**
     * 关注、取关 某个用户（Redis版）
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $userUuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function follow($userUuid)
    {
        // 当前用户ID
        $mime = Auth::user();
        $currId = $mime->id;

        // 目标用户
        $user = $this->userRepository->findBy('uuid', $userUuid);

        if ($user) {
            $fansKey = 'user:' . $user->id . ':fans';       // 我关注某人，我成为他的粉丝
            $followKey = 'user:' . $currId . ':follows';    // 我关注某人，他在我的关注列表

            if ($user->id == $currId) {
                return response()->json(
                    ['message' => __('app.cant_follow_myself')],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            } else {
                if ($this->redisService->zscore($followKey, $user->id)) {
                    // 取关操作
                    $this->redisService->zrem($followKey, $user->id);
                    $this->redisService->zrem($fansKey, $currId);

                    // 更新数据库计数
                    $this->updateFansAndFollowNum($mime, $user, 'cancel');
                    $msg = __('app.already') . __('app.cancel');
                } else {
                    // 关注操作
                    // 最大关注上限
                    if ($mime->followed_num >= $this->maxFollowNum) {
                        return response()->json(
                            ['message' => __('app.cant_exceed_max_follow')],
                            Response::HTTP_UNPROCESSABLE_ENTITY
                        );
                    } else {
                        $this->redisService->zadd($followKey, time(), $user->id);
                        $this->redisService->zadd($fansKey, time(), $currId);

                        // 更新数据库计数
                        $this->updateFansAndFollowNum($mime, $user, '');
                        $msg = __('app.already') . __('app.follow');
                    }
                }

                return response()->json(
                    ['message' => $msg],
                    Response::HTTP_OK
                );
            }
        } else {
            return response()->json(
                ['message' => __('app.user_is_closure')],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * 查看某个用户与我的互粉状态（redis版）
     * 用在查看用户detail时
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $userUuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function status($userUuid)
    {
        // 当前用户ID
        $mime = Auth::user();
        $currId = $mime->id;

        // 目标用户
        $user = $this->userRepository->findBy('uuid', $userUuid);

        if ($user) {
            $myFansKey = 'user:' . $currId . ':fans';          // 他关注我，他成为我的粉丝
            $myFollowsKey = 'user:' . $currId . ':follows';    // 我关注某人，他在我的关注列表

            $iFollowedYou = (bool) $this->redisService->zscore($myFollowsKey, $user->id);
            $youFollowMe = (bool) $this->redisService->zscore($myFansKey, $user->id);

            return response()->json(
                ['data' => ['inMyFollows' => $iFollowedYou, 'inMyFans' => $youFollowMe]],
                Response::HTTP_OK
            );
        } else {
            return response()->json(
                ['message' => __('app.user_is_closure')],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * 获取用户的关注列表（redis版本）
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $userUuid
     * @param $page
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFollowsList($userUuid, $page)
    {
        // 当前用户ID
        $mime = Auth::user();
        $currId = $mime->id;

        // 目标用户
        $user = $this->userRepository->findBy('uuid', $userUuid);

        if ($user) {
            $myFansKey = 'user:' . $currId . ':fans';           // 我的粉丝
            $myFollowsKey = 'user:' . $currId . ':follows';     // 我的关注列表
            $userFollowKey = 'user:' . $user->id . ':follows';  // 目标用户的关注

            $start = ($page - 1) * $this->pageSize;
            $end = $start + $this->pageSize - 1;

            // 目标用户的关注列表
            $userFollowsIdArr = $this->redisService->zrevrange($userFollowKey, $start, $end);
            $userFollowsList = $this->userRepository->getUsersByIdArr($userFollowsIdArr);

            // 看别人
            if ($user->id != $currId) {
                foreach ($userFollowsList as &$userFollow) {
                    $userFollow->inMyFollows = (bool) $this->redisService->zscore($myFollowsKey, $userFollow->id);
                    $userFollow->inMyFans = (bool) $this->redisService->zscore($myFansKey, $userFollow->id);
                }
                unset($userFollow);
            } else {
                // 看的是自己
                foreach ($userFollowsList as &$userFollow) {
                    $userFollow->inMyFollows = true;
                    $userFollow->inMyFans = (bool) $this->redisService->zscore($myFansKey, $userFollow->id);
                }
                unset($userFollow);
            }

            return response()->json(
                ['data' => $userFollowsList],
                Response::HTTP_OK
            );
        } else {
            return response()->json(
                ['message' => __('app.user_is_closure')],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * 获取用户的粉丝列表（redis版本）
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $userUuid
     * @param $page
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFansList($userUuid, $page)
    {
        // 当前用户ID
        $mime = Auth::user();
        $currId = $mime->id;

        // 目标用户
        $user = $this->userRepository->findBy('uuid', $userUuid);

        if ($user) {
            $myFansKey = 'user:' . $currId . ':fans';          // 我的粉丝
            $myFollowsKey = 'user:' . $currId . ':follows';    // 我的关注列表
            $userFansKey = 'user:' . $user->id . ':fans';      // 目标用户的粉丝

            // redis 分页实现
            $start = ($page - 1) * $this->pageSize;
            $end = $start + $this->pageSize - 1;

            // 目标用户的粉丝列表
            $userFansIdArr = $this->redisService->zrevrange($userFansKey, $start, $end);
            $userFansList = $this->userRepository->getUsersByIdArr($userFansIdArr);

            // 看别人
            if ($user->id != $currId) {
                foreach ($userFansList as &$userFan) {
                    $userFan->inMyFollows = (bool) $this->redisService->zscore($myFollowsKey, $userFan->id);
                    $userFan->inMyFans = (bool) $this->redisService->zscore($myFansKey, $userFan->id);
                }
                unset($userFan);
            } else {
                // 看的是自己
                foreach ($userFansList as &$userFan) {
                    $userFan->inMyFollows = (bool) $this->redisService->zscore($myFollowsKey, $userFan->id);
                    $userFan->inMyFans = true;
                }
                unset($userFan);
            }

            return response()->json(
                ['data' => $userFansList],
                Response::HTTP_OK
            );
        } else {
            return response()->json(
                ['message' => __('app.user_is_closure')],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * 关注、取关 操作用户表计数
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $mime    // 当前登录用户对象
     * @param $user    // 被操作用户对象
     * @param $type    // cancel 为取关
     */
    private function updateFansAndFollowNum($mime, $user, $type)
    {
        // 正向操作
        if ($type !== 'cancel') {
            $user->fans_num += 1;
            $mime->followed_num += 1;
            $user->save();
            $mime->save();
        } else {
            // 逆向操作
            $user->fans_num > 0 && $user->fans_num -= 1;
            $mime->followed_num > 0 && $mime->followed_num -= 1;
            $user->save();
            $mime->save();
        }
    }
}
