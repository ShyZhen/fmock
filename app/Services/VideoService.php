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
use App\Repositories\Eloquent\VideoItemRepository;

class VideoService extends Service
{
    private $videoItemRepository;

    private $redisService;

    private $userRepository;

    /**
     * @param VideoItemRepository $videoItemRepository
     * @param RedisService        $redisService
     * @param UserRepository      $userRepository
     */
    public function __construct(
        VideoItemRepository $videoItemRepository,
        RedisService $redisService,
        UserRepository $userRepository
    ) {
        $this->videoItemRepository = $videoItemRepository;
        $this->redisService = $redisService;
        $this->userRepository = $userRepository;
    }

    /**
     * 根据uuid获取我的视频item
     * 轮询转码结果也使用该方法，通过is_transcode=0判断成功与否
     *
     * @param $uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyVideoItemByUuid($uuid)
    {
        $userId = Auth::id();
        $videoItem = $this->videoItemRepository->findBy('uuid', $uuid);

        if ($videoItem) {
            if ($userId == $videoItem->user_id) {
                return response()->json(
                    ['data' => $videoItem],
                    Response::HTTP_OK
                );
            }
        }

        return response()->json(
            ['message' => __('app.no_videos')],
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * 更新视频信息
     *
     * @param $uuid
     * @param $title
     * @param $summary
     * @param $poster
     * @param $isFree
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateVideoItem($uuid, $title, $summary, $poster, $isFree)
    {
        $videoItem = $this->videoItemRepository->findBy('uuid', $uuid);

        if ($videoItem &&
            $videoItem->user_id == Auth::id() &&
            $videoItem->is_transcode == VideoItemRepository::TRANSCODE_SUCCESS
        ) {
            $videoItem->title = $title;
            $videoItem->summary = $summary;
            $videoItem->poster = $poster;
            $videoItem->is_free = $isFree;

            if ($videoItem->save()) {
                return response()->json(
                    ['data' => $videoItem],
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

    /*
     * 获取视频集合 video-collect video表
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $type [hot|all|share|question|dynamite|friend|recruit]
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /*
    public function getAllVideos($type)
    {
        switch ($type) {
            case 'all':
                $posts = $this->postRepository->getNewPost();                   // 全部最新
                break;
            case 'hot':
                $limitDate = Carbon::now()->subDays(90)->toDateString();
                $posts = $this->postRepository->getFavoritePost($limitDate);    // 三个月内点赞最多的热门
                break;
            default:
                $posts = $this->postRepository->getPostByType($type);           // 分类最新
                break;
        }

        if ($posts->count()) {
            foreach ($posts as $post) {
                $post->user_info = $this->handleUserInfo($post->user);
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
    /*
    public function getVideoItemByUuid($uuid)
    {
        $columns = ['id', 'user_id', 'uuid', 'title', 'content', 'type', 'deleted', 'collect_num', 'comment_num', 'like_num', 'dislike_num', 'created_at'];
        $post = $this->postRepository->findBy('uuid', $uuid, $columns);

        if ($post) {
            if ($post->deleted == 'none' || $post->user_id == Auth::id()) {
                $post->user_info = $this->handleUserInfo($post->user);
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

    /*
     * 软删除自己的文章服务
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $uuid
     *
     * @return mixed
     */
    /*
    public function deleteVideoItem($uuid)
    {
        $post = $this->postRepository->findBy('uuid', $uuid);

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
    /*
    public function getUserVideoItems($userUuid)
    {
        $user = $this->userRepository->findBy('uuid', $userUuid);
        if ($user) {

            // 获取评论集合
            $posts = $this->postRepository->getPostsByUserId($user->id);

            if ($posts->count()) {
                foreach ($posts as $post) {
                    $post->user_info = $this->handleUserInfo($post->user);
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


    */
}
