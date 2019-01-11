<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 */
namespace App\Services;

use Illuminate\Http\Response;
use App\Repositories\Eloquent\PostRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\UsersPostsLikeRepository;

class ActionService extends Service
{
    private $userRepository;

    private $postRepository;

    private $usersPostsLikeRepository;

    /**
     * ActionService constructor.
     *
     * @param UserRepository           $userRepository
     * @param PostRepository           $postRepository
     * @param UsersPostsLikeRepository $usersPostsLikeRepository
     */
    public function __construct(
        UserRepository $userRepository,
        PostRepository $postRepository,
        UsersPostsLikeRepository $usersPostsLikeRepository
    ) {
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->usersPostsLikeRepository = $usersPostsLikeRepository;
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
                $post->user_info = $this->postRepository->handleUserInfo($post->user);
                unset($post->user);
            }
        }

        return response()->json(
            ['data' => $posts],
            Response::HTTP_OK
        );
    }

    /**
     * 关注文章操作 并更新post follow_num 表字段
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
            $follow = $this->userRepository->followPost($post->id);

            if (count($follow['attached'])) {
                $post->follow_num += 1;
                $post->save();
            }

            return response()->json(
                ['message' => __('app.follow') . __('app.success')],
                Response::HTTP_OK
            );
        } else {
            return response()->json(
                ['message' => __('app.no_posts')],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * 取消关注 并更新post follow_num 表字段
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unFollow($uuid)
    {
        $post = $this->postRepository->findBy('uuid', $uuid);

        if ($post) {
            if ($this->userRepository->unFollow($post->id)) {
                $post->follow_num -= 1;
                $post->save();
            }

            return response()->json(
                ['message' => __('app.cancel') . __('app.follow') . __('app.success')],
                Response::HTTP_OK
            );
        } else {
            return response()->json(
                ['message' => __('app.no_posts')],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * 赞、取消赞、踩、取消踩
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $uuid
     * @param $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeAction($uuid, $type)
    {
        $field = $type . '_num';
        $post = $this->postRepository->findBy('uuid', $uuid);

        if ($post) {
            $pivot = $this->usersPostsLikeRepository->hasAction($post->id, $type);

            if ($pivot) {
                // 取消
                $this->usersPostsLikeRepository->deleteAction($pivot->id);
                $post->$field -= 1;
                $post->save();
                $message = __('app.cancel') . __('app.success');
            } else {
                // 生成
                $this->usersPostsLikeRepository->makeAction($post->id, $type);
                $post->$field += 1;
                $post->save();
                $message = __('app.success');
            }

            return response()->json(
                ['message' => $message],
                Response::HTTP_OK
            );
        } else {
            return response()->json(
                ['message' => __('app.no_posts')],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * 查询该文章是否存在 赞、踩
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeStatus($uuid)
    {
        $post = $this->postRepository->findBy('uuid', $uuid, ['id']);
        $postId = $post ? $post->id : 0;
        if ($postId) {
            $like = $this->usersPostsLikeRepository->hasAction($postId, 'like');
            $dislike = $this->usersPostsLikeRepository->hasAction($postId, 'dislike');

            return response()->json(
                ['data' => ['like' => $like ? true : false, 'dislike' => $dislike ? true : false]],
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
