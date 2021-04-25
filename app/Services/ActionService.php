<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 */

namespace App\Services;

use App\Repositories\Eloquent\TimelinesFollowRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Eloquent\PostRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\VideoRepository;
use App\Repositories\Eloquent\AnswerRepository;
use App\Repositories\Eloquent\CommentRepository;
use App\Repositories\Eloquent\TimelineRepository;
use App\Repositories\Eloquent\PostsFollowRepository;
use App\Repositories\Eloquent\UsersFollowRepository;
use App\Repositories\Eloquent\VideosFollowRepository;
use App\Repositories\Eloquent\AnswersFollowRepository;
use App\Repositories\Eloquent\PostsCommentsLikeRepository;

class ActionService extends Service
{
    private $userRepository;

    private $postRepository;

    private $timelineRepository;

    private $videoRepository;

    private $answerRepository;

    private $commentRepository;

    private $usersFollowRepository;

    private $postsFollowRepository;

    private $videosFollowRepository;

    private $answersFollowRepository;

    private $timelinesFollowRepository;

    private $postsCommentsLikeRepository;

    /**
     * ActionService constructor.
     *
     * @param UserRepository              $userRepository
     * @param PostRepository              $postRepository
     * @param TimelineRepository          $timelineRepository
     * @param VideoRepository             $videoRepository
     * @param AnswerRepository            $answerRepository
     * @param CommentRepository           $commentRepository
     * @param UsersFollowRepository       $usersFollowRepository
     * @param PostsFollowRepository       $postsFollowRepository
     * @param VideosFollowRepository      $videosFollowRepository
     * @param AnswersFollowRepository     $answersFollowRepository
     * @param TimelinesFollowRepository     $timelinesFollowRepository
     * @param PostsCommentsLikeRepository $postsCommentsLikeRepository
     */
    public function __construct(
        UserRepository $userRepository,
        PostRepository $postRepository,
        TimelineRepository $timelineRepository,
        VideoRepository $videoRepository,
        AnswerRepository $answerRepository,
        CommentRepository $commentRepository,
        UsersFollowRepository $usersFollowRepository,
        PostsFollowRepository $postsFollowRepository,
        VideosFollowRepository $videosFollowRepository,
        AnswersFollowRepository $answersFollowRepository,
        TimelinesFollowRepository $timelinesFollowRepository,
        PostsCommentsLikeRepository $postsCommentsLikeRepository
    ) {
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->timelineRepository = $timelineRepository;
        $this->videoRepository = $videoRepository;
        $this->answerRepository = $answerRepository;
        $this->commentRepository = $commentRepository;
        $this->usersFollowRepository = $usersFollowRepository;
        $this->postsFollowRepository = $postsFollowRepository;
        $this->videosFollowRepository = $videosFollowRepository;
        $this->answersFollowRepository = $answersFollowRepository;
        $this->timelinesFollowRepository = $timelinesFollowRepository;
        $this->postsCommentsLikeRepository = $postsCommentsLikeRepository;
    }

    /**
     * 获取我关注的所有文章、回答、视频
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyCollected($type)
    {
        $responses = $this->userRepository->getMyCollected($type);

        if ($responses->count()) {
            foreach ($responses as $post) {

                // 文章列表不需要如下字段
                unset($post->content);
                unset($post->pivot);

                $post->user_info = $this->handleUserInfo($post->user);
                $post->poster_list = json_decode($post->poster_list, false);
                unset($post->user);
            }
        }

        return response()->json(
            ['data' => $responses],
            Response::HTTP_OK
        );
    }

    /**
     * 收藏文章操作 并更新collect_num 表字段
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $type
     * @param $uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function collect($type, $uuid)
    {
        // postRepository or answerRepository、videoRepository
        $repository = $type . 'Repository';

        $post = $this->$repository->findBy('uuid', $uuid);

        if ($post) {
            $collect = $this->userRepository->collect($post->id, $type);

            if (count($collect['attached'])) {
                $post->collect_num += 1;
                $post->save();
            }

            return response()->json(
                ['message' => __('app.collect') . __('app.success')],
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
     * 取消收藏 并更新collect_num 表字段
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $uuid
     * @param $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unCollect($type, $uuid)
    {
        // postRepository or answerRepository、videoRepository
        $repository = $type . 'Repository';

        $post = $this->$repository->findBy('uuid', $uuid);

        if ($post) {
            if ($this->userRepository->unCollect($post->id, $type)) {
                $post->collect_num > 0 && $post->collect_num -= 1;
                $post->save();
            }

            return response()->json(
                ['message' => __('app.cancel') . __('app.collect') . __('app.success')],
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
     * 对 文章/回答/评论 进行 赞、取消赞、踩、取消踩
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $resourceId
     * @param $type
     * @param $resourceType
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userAction($resourceId, $type, $resourceType)
    {
        $resource = '';
        $field = $type . '_num';

        // 文章和回答都是uuid，评论是id
        if ($resourceType === 'post') {
            $resource = $this->postRepository->findBy('uuid', $resourceId);
        } elseif ($resourceType === 'comment') {
            $resource = $this->commentRepository->find($resourceId);
        } elseif ($resourceType === 'answer') {
            $resource = $this->answerRepository->findBy('uuid', $resourceId);
        } elseif ($resourceType === 'video') {
            $resource = $this->videoRepository->findBy('uuid', $resourceId);
        } elseif ($resourceType === 'timeline') {
            $resource = $this->timelineRepository->findBy('uuid', $resourceId);
        }

        // 目前记录赞和踩都在一张表中，后期可考虑分成单表
        if ($resource) {
            $pivot = $this->postsCommentsLikeRepository->hasAction($resource->id, $type, $resourceType);

            if ($pivot) {
                // 取消
                $this->postsCommentsLikeRepository->deleteAction($pivot->id);
                $resource->$field -= 1;
                $resource->save();
                $message = __('app.cancel') . __('app.success');
            } else {
                // 生成
                $this->postsCommentsLikeRepository->makeAction($resource->id, $type, $resourceType);
                $resource->$field += 1;
                $resource->save();
                $message = __('app.success');
            }

            return response()->json(
                ['message' => $message],
                Response::HTTP_OK
            );
        } else {
            return response()->json(
                ['message' => __('app.no_' . $resourceType . 's')],
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * 查询该 文章/回答/评论 是否存在 赞、踩
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $resourceId
     * @param $resourceType
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function status($resourceId, $resourceType)
    {
        $resource = '';
        $collected = false;
        if ($resourceType === 'post') {
            $resource = $this->postRepository->findBy('uuid', $resourceId, ['id']);
        } elseif ($resourceType === 'comment') {
            $resource = $this->commentRepository->find($resourceId, ['id']);
        } elseif ($resourceType === 'answer') {
            $resource = $this->answerRepository->findBy('uuid', $resourceId, ['id']);
        } elseif ($resourceType === 'video') {
            $resource = $this->videoRepository->findBy('uuid', $resourceId, ['id']);
        } elseif ($resourceType === 'timeline') {
            $resource = $this->timelineRepository->findBy('uuid', $resourceId);
        }

        if ($resource) {
            $like = $this->postsCommentsLikeRepository->hasAction($resource->id, 'like', $resourceType);
            $dislike = $this->postsCommentsLikeRepository->hasAction($resource->id, 'dislike', $resourceType);

            // 查询文章、回答、视频时候的搜藏状态
            $userId = Auth::id();

            // PostsFollowRepository answersFollowRepository videosFollowRepository
            $repository = $resourceType . 's' . 'FollowRepository';
            $collected = $this->$repository
                ->model()::where([
                    'user_id' => $userId,
                    'resource_id' => $resource->id,
                ])
                ->first();

            return response()->json(
                ['data' =>
                    [
                        'liked' => $like ? true : false,
                        'disliked' => $dislike ? true : false,
                        'collected' => $collected ? true : false,
                    ],
                ],
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
     * 获取我关注的用户发的 文章、视频、回答
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTrack($type)
    {
        // 先获取我所有关注者的ID
        $userId = Auth::id();
        $myFollowIds = $this->usersFollowRepository->getAllFollowIds($userId);

        $repository = $type . 'Repository';
        $responses = $this->$repository->getResourcesByUserIdArr($myFollowIds);

        if ($responses->count()) {
            foreach ($responses as $response) {

                // 文章列表不需要如下字段
                unset($response->content);
                unset($response->pivot);

                $response->user_info = $this->handleUserInfo($response->user);
                unset($response->user);
            }
        }

        return response()->json(
            ['data' => $responses],
            Response::HTTP_OK
        );
    }
}
