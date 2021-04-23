<?php
/**
 * 文章控制器
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/25
 * Time: 13:43
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\TimelineService;
use App\Http\Controllers\Controller;

class TimelineController extends Controller
{
    private $timelineService;

    /**
     * @param TimelineService $timelineService
     */
    public function __construct(TimelineService $timelineService)
    {
        $this->timelineService = $timelineService;
    }

    /**
     * 首页列表
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $type
     *
     * @return mixed
     */
    public function getAllTimelines($type)
    {
        if (in_array($type, ['hot', 'new'])) {
            return $this->timelineService->getAllPosts($type);
        } else {
            return response()->json(
                ['message' => __('app.illegal_input')],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * 文章详情
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $uuid
     *
     * @return mixed
     */
    public function getTimelineByUuid($uuid)
    {
        return $this->timelineService->getTimelineByUuid($uuid);
    }

    /**
     * 创建文章
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTimeline(Request $request)
    {
        return $this->timelineService->createPost(
            $request->get('title'),
            json_encode($request->get('poster_list'))
        );
    }

    /**
     * 删除自己的文章
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param $uuid
     *
     * @return mixed
     */
    public function deleteTimeline($uuid)
    {
        return $this->timelineService->deletePost($uuid);
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
    public function userTimeline($userUuid)
    {
        return $this->timelineService->getUserPosts($userUuid);
    }

    /**
     * 举报
     *
     * @param $uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reportTimeline($uuid)
    {
        return $this->timelineService->report($uuid);
    }
}
