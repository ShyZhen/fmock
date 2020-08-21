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
use App\Repositories\Eloquent\PostRepository;
use App\Repositories\Eloquent\UserRepository;

class PostService extends Service
{
    private $postRepository;

    private $redisService;

    private $userRepository;

    /**
     * @param PostRepository $postRepository
     * @param RedisService   $redisService
     * @param UserRepository $userRepository
     */
    public function __construct(
        PostRepository $postRepository,
        RedisService $redisService,
        UserRepository $userRepository
    ) {
        $this->postRepository = $postRepository;
        $this->redisService = $redisService;
        $this->userRepository = $userRepository;
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
    public function getPostByUuid($uuid)
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
     * 创建文章
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $title
     * @param $summary
     * @param $poster
     * @param $content
     * @param $anonymous
     * @param $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPost($title, $summary, $poster, $content, $anonymous, $type)
    {
        $userId = Auth::id();

        if ($this->redisService->isRedisExists('post:user:' . $userId)) {
            return response()->json(
                ['message' => __('app.action_ttl') . $this->redisService->getRedisTtl('post:user:' . $userId) . 's'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } else {
            $uuid = self::uuid('post-');
            $post = $this->postRepository->create([
                'uuid' => $uuid,
                'user_id' => $anonymous ? 0 : $userId,
                'title' => $title,
                'summary' => $summary,
                'poster' => str_replace($this->imageProcess, $this->imagePosterProcess, $poster),
                'content' => $content,
                'type' => $type,
            ]);

            if ($post) {
                // 写入限制 2分钟一次
                $this->redisService->setRedis('post:user:' . $userId, 'create', 'EX', 120);

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
        $post = $this->postRepository->findBy('uuid', $uuid);

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
    public function getUserPosts($userUuid)
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
}
