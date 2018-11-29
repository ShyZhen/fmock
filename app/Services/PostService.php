<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: Response::HTTP_CREATED8/8/25
 * Time: 23:25
 */
namespace App\Services;

use App\Repositories\Eloquent\PostRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PostService extends Service
{
    private $postRepository;

    private $redisService;

    /**
     * @param PostRepository $postRepository
     * @param RedisService   $redisService
     */
    public function __construct(
        PostRepository $postRepository,
        RedisService $redisService
    ) {
        $this->postRepository = $postRepository;
        $this->redisService = $redisService;
    }

    /**
     * 获取首页文章列表 URL可选参数sort,page
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param null $sort
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllPosts($sort = null)
    {
        switch ($sort) {
            case 'post-new':
                $posts = $this->postRepository->getNewPost();
                break;
            case 'post-hot':
                $posts = $this->postRepository->getFavoritePost();
                break;
            case 'post-anonymous':
                $posts = $this->postRepository->getAnonymousPost();
                break;
            default:
                $posts = [];
        }

        if ($posts->count()) {
            foreach ($posts as $post) {
                $post->userInfo = $this->postRepository->handleUserInfo($post->user);
                $post->content = str_limit($post->content, 400, '...');
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
        $post = $this->postRepository->findBy('uuid', $uuid);

        if ($post) {
            if ($post->deleted == 'none' || $post->user_id == Auth::id()) {
                $post->userInfo = $this->postRepository->handleUserInfo($post->user);

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
     * @param $content
     * @param $anonymous
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPost($title, $content, $anonymous)
    {
        $userId = Auth::id();

        if ($this->redisService->isRedisExists('post:user:'.$userId)) {
            return response()->json(
                ['message' => __('app.action_ttl').$this->redisService->getRedisTtl('post:user:'.$userId).'s'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } else {
            $this->redisService->setRedis('post:user:'.$userId, 'create', 'EX', 120);
            $uuid = $this->uuid('post-');
            $post = $this->postRepository->create([
                'uuid' => $uuid,
                'user_id' => $anonymous ? 0 : $userId,
                'title' => $title,
                'content' => $content,
            ]);

            if ($post) {
                $post->userInfo = $this->postRepository->handleUserInfo($post->user);

                return response()->json(
                    ['data' => $post],
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
     * @param $content
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePost($uuid, $content)
    {
        $post = $this->postRepository->findBy('uuid', $uuid);

        if ($post && $post->user_id == Auth::id()) {
            $post->content = $content;

            if ($post->save()) {
                $post->userInfo = $this->postRepository->handleUserInfo($post->user);

                return response()->json(
                    ['data' => $post],
                    Response::HTTP_CREATED
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

        if ($post && $post->user_id == Auth::id()) {
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
}
