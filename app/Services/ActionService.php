<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 */
namespace App\Services;

use App\Repositories\Eloquent\PostRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Http\Response;

class ActionService extends Service
{
    private $userRepository;

    private $postRepository;

    /**
     * ActionService constructor.
     *
     * @param UserRepository $userRepository
     * @param PostRepository $postRepository
     */
    public function __construct(
        UserRepository $userRepository,
        PostRepository $postRepository
    ) {
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
    }

    /**
     * 获取我关注的所有文章
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyFollowedPosts()
    {
        $posts = $this->userRepository->getMyFollowedPosts();

        if ($posts->count()) {
            foreach ($posts as $post) {
                $post->userinfo = $this->userRepository->getUserInfoById($post->user_id);
            }
        }

        return response()->json(
            ['data' => $posts],
            Response::HTTP_OK
        );
    }

    /**
     * 关注文章操作
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function followPost($uuid)
    {
        $post = $this->postRepository->findBy('uuid', $uuid);

        if ($post) {
            $this->userRepository->followPost($post->id);

            return response()->json(
                ['message' => __('app.follow').__('app.success')],
                Response::HTTP_OK
            );
        } else {
            return response()->json(
                ['message' => __('app.no_posts')],
                Response::HTTP_NOT_FOUND
            );
        }
    }
}
