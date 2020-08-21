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
use App\Repositories\Eloquent\AnswerRepository;

class AnswerService extends Service
{
    private $answerRepository;

    private $redisService;

    private $userRepository;

    private $postRepository;

    /**
     * @param RedisService     $redisService
     * @param UserRepository   $userRepository
     * @param PostRepository   $postRepository
     * @param AnswerRepository $answerRepository
     */
    public function __construct(
        RedisService $redisService,
        UserRepository $userRepository,
        PostRepository $postRepository,
        AnswerRepository $answerRepository
    ) {
        $this->redisService = $redisService;
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->answerRepository = $answerRepository;
    }

    /**
     * 根据文章ID获取回答
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $postUuid
     * @param $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAnswerByPostUuid($postUuid, $type)
    {
        $post = $this->postRepository->findBy('uuid', $postUuid);
        if ($post) {

            // 获取回答集合
            if ($type == 'hot') {
                $limitDate = Carbon::now()->subDays(90)->toDateString();
                $answers = $this->answerRepository->getFavoriteAnswer($post->id, $limitDate);
            } else {
                $answers = $this->answerRepository->getNewAnswer($post->id);
            }

            // 处理预加载的用户信息
            if ($answers->count()) {
                foreach ($answers as $answer) {
                    $answer->user_info = $this->handleUserInfo($answer->user);
                    unset($answer->user);
                    unset($answer->user_id);
                }
            }

            return response()->json(
                ['data' => $answers],
                Response::HTTP_OK
            );
        }

        return response()->json(
            ['message' => __('app.no_posts')],
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * 获取（回答）文章详情
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAnswerByUuid($uuid)
    {
        $columns = ['id', 'user_id', 'uuid', 'title', 'content', 'collect_num', 'comment_num', 'like_num', 'dislike_num', 'deleted', 'created_at'];
        $answer = $this->answerRepository->findBy('uuid', $uuid, $columns);

        if ($answer) {
            if ($answer->deleted == 'none' || $answer->user_id == Auth::id()) {
                $answer->user_info = $this->handleUserInfo($answer->user);
                unset($answer->user);
                unset($answer->user_id);

                return response()->json(
                    ['data' => $answer],
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
     * 创建(回答)文章
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $postUuid
     * @param $title
     * @param $summary
     * @param $poster
     * @param $content
     * @param $anonymous
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createAnswer($postUuid, $title, $summary, $poster, $content, $anonymous)
    {
        $post = $this->postRepository->findBy('uuid', $postUuid);

        if ($post) {
            $userId = Auth::id();

            if ($this->redisService->isRedisExists('answer:user:' . $userId)) {
                return response()->json(
                    ['message' => __('app.action_ttl') . $this->redisService->getRedisTtl('answer:user:' . $userId) . 's'],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            } else {
                $uuid = self::uuid('answer-');
                $answer = $this->answerRepository->create([
                    'uuid' => $uuid,
                    'user_id' => $anonymous ? 0 : $userId,
                    'post_id' => $post->id,
                    'title' => $title,
                    'summary' => $summary,
                    'poster' => $poster,
                    'content' => $content,
                ]);

                if ($answer) {
                    // 写入限制 2分钟一次
                    $this->redisService->setRedis('answer:user:' . $userId, 'create', 'EX', 120);

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
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAnswer($uuid, $summary, $poster, $content, $anonymous)
    {
        $answer = $this->answerRepository->findBy('uuid', $uuid);

        if ($answer && $answer->user_id == Auth::id()) {
            $answer->summary = $summary;
            $answer->poster = $poster;
            $answer->content = $content;
            if ($anonymous) {
                $answer->user_id = 0;
            }

            if ($answer->save()) {
                $answer->user_info = $this->handleUserInfo($answer->user);
                unset($answer->user);
                unset($answer->user_id);

                return response()->json(
                    ['data' => $answer],
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
    public function deleteAnswer($uuid)
    {
        $answer = $this->answerRepository->findBy('uuid', $uuid);

        if ($answer && $answer->user_id == Auth::id() && $answer->deleted == 'none') {
            $answer->deleted = 'yes';
            if ($answer->save()) {
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
     * 获取某个用户的所有(回答)文章列表
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $userUuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserAnswers($userUuid)
    {
        $user = $this->userRepository->findBy('uuid', $userUuid);
        if ($user) {
            $answers = $this->answerRepository->getAnswersByUserId($user->id);

            if ($answers->count()) {
                foreach ($answers as $answer) {
                    $answer->user_info = $this->handleUserInfo($answer->user);
                    unset($answer->user);
                }
            }

            return response()->json(
                ['data' => $answers],
                Response::HTTP_OK
            );
        }

        return response()->json(
            ['message' => __('app.user_is_closure')],
            Response::HTTP_NOT_FOUND
        );
    }
}
