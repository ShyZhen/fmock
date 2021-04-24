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
use App\Repositories\Eloquent\UsersFollowRepository;

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

    private $usersFollowRepository;

    /**
     * ActionService constructor.
     *
     * @param RedisService          $redisService
     * @param UserRepository        $userRepository
     * @param UsersFollowRepository $usersFollowRepository
     */
    public function __construct(
        RedisService $redisService,
        UserRepository $userRepository,
        UsersFollowRepository $usersFollowRepository
    ) {
        $this->redisService = $redisService;
        $this->userRepository = $userRepository;
        $this->usersFollowRepository = $usersFollowRepository;
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
        $mine = Auth::user();
        $currId = $mine->id;

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
                    $this->updateFansAndFollowNum($mine, $user, 'cancel');
                    $msg = __('app.already') . __('app.cancel');
                } else {
                    // 关注操作

                    // 每日关注上限
                    if ($this->verifyFollowingLimit($currId)) {
                        return response()->json(
                            ['message' => __('app.exceed_day_max_follow')],
                            Response::HTTP_FORBIDDEN
                        );
                    }

                    // 最大关注上限
                    if ($mine->followed_num >= $this->maxFollowNum) {
                        return response()->json(
                            ['message' => __('app.cant_exceed_max_follow')],
                            Response::HTTP_UNPROCESSABLE_ENTITY
                        );
                    } else {
                        $this->redisService->zadd($followKey, time(), $user->id);
                        $this->redisService->zadd($fansKey, time(), $currId);

                        // 更新数据库计数
                        $this->updateFansAndFollowNum($mine, $user, '');
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
        $mine = Auth::user();
        $currId = $mine->id;

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
        $mine = Auth::user();
        $currId = $mine->id;

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
        $mine = Auth::user();
        $currId = $mine->id;

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
     * 关注、取关 某个用户（Mysql版）
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $userUuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function followDB($userUuid)
    {
        // 当前用户ID
        $mine = Auth::user();
        $currId = $mine->id;

        // 目标用户
        $user = $this->userRepository->findBy('uuid', $userUuid);

        if ($user) {
            if ($user->id == $currId) {
                return response()->json(
                    ['message' => __('app.cant_follow_myself')],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            } else {
                $follow = $this->usersFollowRepository->isFollowed($currId, $user->id);

                // 取关操作
                if ($follow) {
                    // 查看是否互关状态
                    if ($follow->both_status == 'yes') {
                        $this->usersFollowRepository->updateFollowStatus($user->id, $currId, 'none');
                    }

                    $follow->delete();
                    // 更新数据库计数
                    $this->updateFansAndFollowNum($mine, $user, 'cancel');
                    $msg = __('app.already') . __('app.cancel');
                } else {
                    // 关注操作
                    // 每日关注上限
                    if ($this->verifyFollowingLimit($currId)) {
                        return response()->json(
                            ['message' => __('app.exceed_day_max_follow')],
                            Response::HTTP_FORBIDDEN
                        );
                    }

                    // 最大关注上限
                    if ($mine->followed_num >= $this->maxFollowNum) {
                        return response()->json(
                            ['message' => __('app.cant_exceed_max_follow')],
                            Response::HTTP_UNPROCESSABLE_ENTITY
                        );
                    } else {
                        // 查看他是否已经关注了我
                        $youFollowedMe = $this->usersFollowRepository->isFollowed($user->id, $currId);

                        $bothStatus = $youFollowedMe ? 'yes' : 'none';
                        $createFollow = $this->usersFollowRepository->create([
                            'master_user_id' => $user->id,
                            'following_user_id' => $currId,
                            'both_status' => $bothStatus,
                        ]);

                        // 如果他已经关注了我，则修改双方状态为互关
                        if ($createFollow && $youFollowedMe) {
                            $youFollowedMe->both_status = $bothStatus;
                            $youFollowedMe->save();
                        }

                        // 更新数据库计数
                        $this->updateFansAndFollowNum($mine, $user, '');
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
     * 查看某个用户与我的互粉状态（MYSQL版）
     * 用在查看用户detail时
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $userUuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statusDB($userUuid)
    {
        // 当前用户ID
        $mine = Auth::user();
        $currId = $mine->id;

        // 目标用户
        $user = $this->userRepository->findBy('uuid', $userUuid);

        if ($user) {
            $iFollowedYou = (bool) $this->usersFollowRepository->isFollowed($currId, $user->id);
            $youFollowMe = (bool) $this->usersFollowRepository->isFollowed($user->id, $currId);

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
     * 获取用户的关注列表（MYSQL版本）
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $userUuid
     * @param $page
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFollowsListDB($userUuid, $page)
    {
        // 当前用户ID
        $mine = Auth::user();
        $currId = $mine->id;

        // 目标用户
        $user = $this->userRepository->findBy('uuid', $userUuid);

        if ($user) {
            $start = ($page - 1) * $this->pageSize;

            // 目标用户的关注列表
            $userFollows = $this->usersFollowRepository->getSomeoneFollows($user->id, $start, $this->pageSize);
            $userFollowsIdArr = $userFollows->pluck('master_user_id');
            $userFollowsList = $this->userRepository->getUsersByIdArr($userFollowsIdArr);

            // 看别人
            if ($user->id != $currId) {

                // 找到这些人中 也同时关注了我的 (先注释，优化性能)
//                $myFansArr = $this->usersFollowRepository->getSomeoneFansByIdArr($currId, $userFollowsIdArr);
                // 找到这些人中 我同时关注了的
                $myFollowedArr = $this->usersFollowRepository->getSomeoneFollowsByIdArr($currId, $userFollowsIdArr);

                foreach ($userFollowsList as &$userFollow) {
                    $userFollow->inMyFans = (bool) false;
                    $userFollow->inMyFollows = (bool) false;

                    // 同时关注了我 (先注释，优化性能)
//                    foreach ($myFansArr as $myFans) {
//                        if ($myFans->following_user_id == $userFollow->id) {
//                            $userFollow->inMyFans = (bool) true;
//                        }
//                    }

                    // 我同时关注了他（她）
                    foreach ($myFollowedArr as $myFollower) {
                        if ($myFollower->master_user_id == $userFollow->id) {
                            $userFollow->inMyFollows = (bool) true;
                        }
                    }
                }
                unset($userFollow);
            } else {
                // 看的是自己
                // 查看状态是否互粉，即可知道他们关注我没有
                foreach ($userFollowsList as &$userFollow) {
                    foreach ($userFollows as $userFollowerItem) {
                        if ($userFollow->id == $userFollowerItem->master_user_id) {
                            $userFollow->both_status = $userFollowerItem->both_status;
                        }
                    }
                }
                unset($userFollow);

                // 最终遍历数据，减少数据库请求
                foreach ($userFollowsList as &$userFollow) {
                    $userFollow->inMyFollows = true;
                    $userFollow->inMyFans = $userFollow->both_status == 'yes' ? true : false;
                    unset($userFollow->both_status);
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
     * 获取用户的粉丝列表（MYSQL版本）
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $userUuid
     * @param $page
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFansListDB($userUuid, $page)
    {
        // 当前用户ID
        $mine = Auth::user();
        $currId = $mine->id;

        // 目标用户
        $user = $this->userRepository->findBy('uuid', $userUuid);

        if ($user) {
            $start = ($page - 1) * $this->pageSize;

            // 目标用户的粉丝列表
            $userFans = $this->usersFollowRepository->getSomeoneFans($user->id, $start, $this->pageSize);
            $userFansIdArr = $userFans->pluck('following_user_id');
            $userFansList = $this->userRepository->getUsersByIdArr($userFansIdArr);

            // 看别人
            if ($user->id != $currId) {

                // 找到这些人中 也同时关注了我的 (先注释，优化性能)
//                $myFansArr = $this->usersFollowRepository->getSomeoneFansByIdArr($currId, $userFansIdArr);
                // 找到这些人中 我同时关注了的
                $myFollowedArr = $this->usersFollowRepository->getSomeoneFollowsByIdArr($currId, $userFansIdArr);

                foreach ($userFansList as &$userFan) {
//                    $userFan->inMyFans = (bool) false;
                    $userFan->inMyFollows = (bool) false;

                    // 同时关注了我 (先注释，优化性能)
//                    foreach ($myFansArr as $myFan) {
//                        if ($myFan->following_user_id == $userFan->id) {
//                            $userFan->inMyFans = (bool) true;
//                        }
//                    }

                    // 我同时关注了他（她）
                    foreach ($myFollowedArr as $myFollower) {
                        if ($myFollower->master_user_id == $userFan->id) {
                            $userFan->inMyFollows = (bool) true;
                        }
                    }
                }
                unset($userFan);
            } else {
                // 看的是自己
                // 查看状态是否互粉，即可知道他们关注我没有
                foreach ($userFansList as &$userFan) {
                    foreach ($userFans as $userFanItem) {
                        if ($userFan->id == $userFanItem->following_user_id) {
                            $userFan->both_status = $userFanItem->both_status;
                        }
                    }
                }
                unset($userFan);

                // 最终遍历数据，减少数据库请求
                foreach ($userFansList as &$userFan) {
                    $userFan->inMyFollows = $userFan->both_status == 'yes' ? true : false;
                    $userFan->inMyFans = true;
                    unset($userFan->both_status);
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
     * @param $mine    // 当前登录用户对象
     * @param $user    // 被操作用户对象
     * @param $type    // cancel 为取关
     */
    private function updateFansAndFollowNum($mine, $user, $type)
    {
        // 正向操作
        if ($type !== 'cancel') {
            $user->fans_num += 1;
            $mine->followed_num += 1;
            $user->save();
            $mine->save();
        } else {
            // 逆向操作
            $user->fans_num > 0 && $user->fans_num -= 1;
            $mine->followed_num > 0 && $mine->followed_num -= 1;
            $user->save();
            $mine->save();
        }
    }

    /**
     * 每天最多进行关注30个人
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $currId
     *
     * @return bool
     */
    private function verifyFollowingLimit($currId)
    {
        if ($this->redisService->isRedisExists('following:user:' . $currId)) {
            $this->redisService->redisIncr('following:user:' . $currId);

            if ($this->redisService->getRedis('following:user:' . $currId) > 30) {
                // 本地环境关闭该限制
                if (env('APP_ENV') == 'local') {
                    return false;
                }

                return true;
            }

            return false;
        } else {
            $this->redisService->setRedis('following:user:' . $currId, 1, 'EX', 86400);

            return false;
        }
    }
}
