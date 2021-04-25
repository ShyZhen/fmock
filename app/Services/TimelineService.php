<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: Response::HTTP_CREATED8/8/25
 * Time: 23:25
 */

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Services\BaseService\RedisService;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\ReportRepository;
use App\Repositories\Eloquent\TimelineRepository;

class TimelineService extends Service
{
    private $timelineRepository;

    private $redisService;

    private $userRepository;

    private $reportRepository;

    private $securityCheckService;

    /**
     * @param TimelineRepository   $timelineRepository
     * @param RedisService         $redisService
     * @param UserRepository       $userRepository
     * @param ReportRepository     $reportRepository
     * @param SecurityCheckService $securityCheckService
     */
    public function __construct(
        TimelineRepository $timelineRepository,
        RedisService $redisService,
        UserRepository $userRepository,
        ReportRepository $reportRepository,
        SecurityCheckService $securityCheckService
    ) {
        $this->timelineRepository = $timelineRepository;
        $this->redisService = $redisService;
        $this->userRepository = $userRepository;
        $this->reportRepository = $reportRepository;
        $this->securityCheckService = $securityCheckService;
    }

    /**
     * 获取首页文章列表 URL可选参数sort,page
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $type [hot|all|share|question|dynamite|friend|recruit]
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllPosts($type)
    {
        switch ($type) {
            case 'new':
                $posts = $this->timelineRepository->getNewPost();                   // 全部最新
                break;
            case 'hot':
                $limitDate = Carbon::now()->subDays(90)->toDateString();
                $posts = $this->timelineRepository->getFavoritePost($limitDate);    // 三个月内点赞最多的热门
                break;
            default:
                $posts = [];
                break;
        }

        if ($posts->count()) {
            foreach ($posts as $post) {
                $post->user_info = $this->handleUserInfo($post->user);
                $post->poster_list = json_decode($post->poster_list, false);
                unset($post->user);
                unset($post->user_id);
            }
        }

        return response()->json(
            ['data' => $posts],
            Response::HTTP_OK
        );
    }

    /**
     * 获取文章详情
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTimelineByUuid($uuid)
    {
        $columns = ['id', 'user_id', 'uuid', 'title', 'deleted', 'poster_list', 'collect_num', 'comment_num', 'like_num', 'dislike_num', 'created_at'];
        $post = $this->timelineRepository->findBy('uuid', $uuid, $columns);

        if ($post) {
            if ($post->deleted == 'none' || $post->user_id == Auth::id()) {
                $post->user_info = $this->handleUserInfo($post->user);
                $post->poster_list = json_decode($post->poster_list, false);
                unset($post->user);
                unset($post->user_id);

                return response()->json(
                    ['data' => $post],
                    Response::HTTP_OK
                );
            }
        }

        return response()->json(
            ['message' => __('app.no_posts')],
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * 创建文章
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $title
     * @param $posterList
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPost($title, $posterList)
    {
        $userId = Auth::id();

        if ($this->redisService->isRedisExists('timeline:user:' . $userId)) {
            return response()->json(
                ['message' => __('app.action_ttl') . $this->redisService->getRedisTtl('timeline:user:' . $userId) . 's'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } else {
            // 敏感词校验
            if ($title) {
                if (!$this->securityCheckService->stringCheck($title)) {
                    return response()->json(
                        ['message' => __('app.has_sensitive_words')],
                        Response::HTTP_UNPROCESSABLE_ENTITY
                    );
                }
            }

            $uuid = self::uuid('timeline-');
            $post = $this->timelineRepository->create([
                'uuid' => $uuid,
                'user_id' => $userId,
                'title' => $title,
                'poster_list' => $posterList,
            ]);

            if ($post) {
                // 写入限制 1分钟一次
                $this->redisService->setRedis('timeline:user:' . $userId, 'create', 'EX', 60);

                return response()->json(
                    ['data' => $uuid],
                    Response::HTTP_CREATED
                );
            }

            return response()->json(
                ['message' => __('app.try_again')],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * 更新自己的文章服务
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $uuid
     * @param $summary
     * @param $poster
     * @param $content
     * @param $anonymous
     * @param $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePost($uuid, $summary, $poster, $content, $anonymous, $type)
    {
        $post = $this->timelineRepository->findBy('uuid', $uuid);

        if ($post && $post->user_id == Auth::id()) {
            $post->summary = $summary;
            $post->poster = $poster;
            $post->content = $content;
            $post->type = $type;
            if ($anonymous) {
                $post->user_id = 0;
            }

            if ($post->save()) {
                $post->user_info = $this->handleUserInfo($post->user);
                $post->poster_list = json_decode($post->poster_list, false);
                unset($post->user);
                unset($post->user_id);

                return response()->json(
                    ['data' => $post],
                    Response::HTTP_OK
                );
            }

            return response()->json(
                ['message' => __('app.try_again')],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return response()->json(
            ['message' => __('app.no_posts')],
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * 软删除自己的文章服务
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $uuid
     *
     * @return mixed
     */
    public function deletePost($uuid)
    {
        $post = $this->timelineRepository->findBy('uuid', $uuid);

        if ($post && $post->user_id == Auth::id() && $post->deleted == 'none') {
            $post->deleted = 'yes';
            if ($post->save()) {
                return response()->json(
                    null,
                    Response::HTTP_NO_CONTENT
                );
            }

            return response()->json(
                ['message' => __('app.try_again')],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return response()->json(
            ['message' => __('app.no_posts')],
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * 获取某个用户的所有文章列表
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $userUuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserPosts($userUuid)
    {
        $user = $this->userRepository->findBy('uuid', $userUuid);
        if ($user) {
            $posts = $this->timelineRepository->getPostsByUserId($user->id);

            if ($posts->count()) {
                foreach ($posts as $post) {
                    $post->user_info = $this->handleUserInfo($post->user);
                    $post->poster_list = json_decode($post->poster_list, false);
                    unset($post->user);
                }
            }

            return response()->json(
                ['data' => $posts],
                Response::HTTP_OK
            );
        }

        return response()->json(
            ['message' => __('app.user_is_closure')],
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * 举报操作
     *
     * @param $uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function report($uuid): \Illuminate\Http\JsonResponse
    {
        $type = 'timeline';
        $post = $this->timelineRepository->findBy('uuid', $uuid);

        if ($post) {
            $userId = Auth::id();

            // 防止重复举报
            $hasReport = $this->reportRepository->hasReport($userId, $post->id, $type);
            if ($hasReport->count()) {
                return response()->json(
                    ['message' => __('app.has_report')],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            // 写举报信息
            $report = $this->reportRepository->create([
                'user_id' => $userId,
                'resource_id' => $post->id,
                'reason' => '',
                'type' => $type,
            ]);

            if ($report) {
                return response()->json(
                    ['data' => $report],
                    Response::HTTP_OK
                );
            }

            return response()->json(
                ['message' => __('app.try_again')],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return response()->json(
            ['message' => __('app.no_posts')],
            Response::HTTP_NOT_FOUND
        );
    }
}
