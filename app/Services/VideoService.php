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
            ['message' => __('app.no_videos')],
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * 软删除自己的文章服务
     *
     * @param $uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteVideoItem($uuid)
    {
        $videoItem = $this->videoItemRepository->findBy('uuid', $uuid);

        if ($videoItem && $videoItem->user_id == Auth::id() && $videoItem->deleted == 'none') {
            $videoItem->deleted = 'yes';
            if ($videoItem->save()) {
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
            ['message' => __('app.no_videos')],
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
}
