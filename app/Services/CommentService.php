<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: Response::HTTP_CREATED8/8/25
 * Time: 23:25
 */

namespace App\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Services\BaseService\RedisService;
use App\Repositories\Eloquent\PostRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\AnswerRepository;
use App\Repositories\Eloquent\CommentRepository;
use App\Repositories\Eloquent\TimelineRepository;

class CommentService extends Service
{
    private $postRepository;

    private $timelineRepository;

    private $answerRepository;

    private $commentRepository;

    private $redisService;

    private $userRepository;

    private $securityCheckService;

    /**
     * CommentService constructor.
     *
     * @param PostRepository       $postRepository
     * @param TimelineRepository   $timelineRepository
     * @param AnswerRepository     $answerRepository
     * @param CommentRepository    $commentRepository
     * @param RedisService         $redisService
     * @param UserRepository       $userRepository
     * @param SecurityCheckService $securityCheckService
     */
    public function __construct(
        PostRepository $postRepository,
        TimelineRepository $timelineRepository,
        AnswerRepository $answerRepository,
        CommentRepository $commentRepository,
        RedisService $redisService,
        UserRepository $userRepository,
        SecurityCheckService $securityCheckService
    ) {
        $this->postRepository = $postRepository;
        $this->timelineRepository = $timelineRepository;
        $this->answerRepository = $answerRepository;
        $this->commentRepository = $commentRepository;
        $this->redisService = $redisService;
        $this->userRepository = $userRepository;
        $this->securityCheckService = $securityCheckService;
    }

    /**
     * 处理评论信息
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param object $comments
     *
     * @return object
     */
    private function handleComments($comments)
    {
        foreach ($comments as $comment) {
            $comment->user_info = $this->handleUserInfo($comment->user);
            unset($comment->user);

            // 处理已经删除的评论
            if ($comment->deleted == 'yes') {
                $comment->content = __('app.comment_has_deleted');
            }

            // 处理父级评论
            $comment->parent_info = '';
            if ($comment->parent_id) {
                $comment->parent_info = $this->commentRepository->getParentComment($comment->parent_id);

                // 处理父级预加载用户信息
                $comment->parent_info->user_info = $this->handleUserInfo($comment->parent_info->user);
                unset($comment->parent_info->user);

                // 处理父级已经删除的评论
                if ($comment->parent_info->deleted == 'yes') {
                    $comment->parent_info->content = __('app.comment_has_deleted');
                }
            }
        }

        return $comments;
    }

    /**
     * 获取某篇文章的评论 (类似网易云音乐的评论系统)
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $postUuid
     * @param $type
     * @param $sort
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllComments($type, $postUuid, $sort)
    {
        // postRepository or answerRepository
        $repository = $type . 'Repository';
        $post = $this->$repository->findBy('uuid', $postUuid);

        if ($post) {

            // 获取评论集合
            if ($sort == 'hot') {
                $comments = $this->commentRepository->getAllHotComments($post->id, $type);
            } else {
                $comments = $this->commentRepository->getAllNewComments($post->id, $type);
            }

            // 处理评论信息
            if ($comments->count()) {
                $comments = $this->handleComments($comments);
            }

            return response()->json(
                ['data' => $comments],
                Response::HTTP_OK
            );
        }

        return response()->json(
            ['message' => __('app.no_posts')],
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * 写评论/回复 每分钟请求一次
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $postUuid
     * @param $parentId
     * @param $content
     * @param $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createComment($postUuid, $parentId, $content, $type)
    {
        $userId = Auth::id();

        if ($this->redisService->isRedisExists('comment:user:' . $userId)) {
            return response()->json(
                ['message' => __('app.action_ttl') . $this->redisService->getRedisTtl('comment:user:' . $userId) . 's'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } else {
            // 简单验证

            // 敏感词校验
            if (!$this->securityCheckService->stringCheck($content)) {
                return response()->json(
                    ['message' => __('app.has_sensitive_words')],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            // postRepository or answerRepository
            $repository = $type . 'Repository';
            $post = $this->$repository->findBy('uuid', $postUuid);

            $parentComment = null;
            if ($parentId !== 0) {
                $parentComment = $this->commentRepository->find($parentId);
            }

            if ($post) {
                $comment = $this->commentRepository->create([
                    'type' => $type,
                    'resource_id' => $post->id,
                    'resource_uuid' => $postUuid,    // 方便通过评论找到原始文章
                    'parent_id' => $parentComment ? $parentComment->id : 0,
                    'user_id' => $userId,
                    'content' => $content,
                ]);

                if ($comment) {
                    // 写入限制 1分钟一次
                    $this->redisService->setRedis('comment:user:' . $userId, 'create', 'EX', 20);

                    // 更新评论数量,回复也算在内
                    $post->comment_num += 1;
                    $post->save();

                    // 处理父级
                    $comment = $this->handleComments([$comment]);

                    return response()->json(
                        ['data' => $comment],
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
    }

    /**
     * 删除自己的评论
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteComment($id)
    {
        $comment = $this->commentRepository->find($id);

        if ($comment && $comment->user_id == Auth::id()) {
            $comment->deleted = 'yes';
            if ($comment->save()) {
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
            ['message' => __('app.no_comments')],
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * 获取某个用户全部热评
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $userUuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserComments($userUuid)
    {
        $user = $this->userRepository->findBy('uuid', $userUuid);
        if ($user) {

            // 获取评论集合
            $comments = $this->commentRepository->getCommentsByUserId($user->id);

            // 处理预加载的用户信息
            if ($comments->count()) {
                $comments = $this->handleComments($comments);
            }

            return response()->json(
                ['data' => $comments],
                Response::HTTP_OK
            );
        }

        return response()->json(
            ['message' => __('app.user_is_closure')],
            Response::HTTP_NOT_FOUND
        );
    }
}
