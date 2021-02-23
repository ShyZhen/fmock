<?php
/**
 * 视频处理类 item collect等操作
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/9/4
 * Time: 16:45
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\VideoService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
{
    private $videoService;

    /**
     * FileController constructor.
     *
     * @param VideoService $videoService
     */
    public function __construct(VideoService $videoService)
    {
        $this->videoService = $videoService;
    }

    /**
     * 轮询转码结果,通过is_transcode=0判断成功与否
     *
     * @param $uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyVideoItemByUuid($uuid)
    {
        return $this->videoService->getMyVideoItemByUuid($uuid);
    }

    /**
     * 更新视频信息
     *
     * @param Request $request
     * @param $uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateVideoItem(Request $request, $uuid)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:64',
            'summary' => 'required|max:80',
            'poster' => 'required|max:128',
            'is_free' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->videoService->updateVideoItem(
                $uuid,
                $request->get('title'),
                $request->get('summary'),
                $request->get('poster'),
                $request->get('is_free')
            );
        }
    }

    /**
     * 软删除
     *
     * @param $uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteVideoItem($uuid)
    {
        return $this->videoService->deleteVideoItem($uuid);
    }
}
