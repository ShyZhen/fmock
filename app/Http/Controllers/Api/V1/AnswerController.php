<?php
/**
 * 回答 控制器
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2019/8/26
 * Time: 11:23
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\AnswerService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AnswerController extends Controller
{
    private $answerService;

    /**
     * AnswerController constructor.
     *
     * @param AnswerService $answerService
     */
    public function __construct(AnswerService $answerService)
    {
        $this->answerService = $answerService;
    }

    /**
     * 根据文章ID获取回答列表（类似文章列表）
     *
     * @author z00455118 <zhenhuaixiu@huawei.com>
     *
     * @param $postUuid
     * @param string $type
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAnswerByPostUuid($postUuid, $type = 'new')
    {
        return $this->answerService->getAnswerByPostUuid($postUuid, $type);
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
    public function getAnswerByUuid($uuid)
    {
        return $this->answerService->getAnswerByUuid($uuid);
    }

    /**
     * 创建（答案）文章
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createAnswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_uuid' => 'required|max:64',
            'title' => 'required|max:64',
            'summary' => 'present|max:80',
            'poster' => 'present|max:128',
            'content' => 'required|max:' . env('CONTENT_NUM'),
            'anonymous' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->answerService->createAnswer(
                $request->get('post_uuid'),
                $request->get('title'),
                $request->get('summary'),
                $request->get('poster'),
                $request->get('content'),
                $request->get('anonymous')
            );
        }
    }

    /**
     * 更新自己的(回答)文章
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param Request $request
     * @param $uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAnswer(Request $request, $uuid)
    {
        $validator = Validator::make($request->all(), [
            'summary' => 'present|max:80',
            'poster' => 'present|max:128',
            'content' => 'required|max:' . env('CONTENT_NUM'),
            'anonymous' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->answerService->updateAnswer(
                $uuid,
                $request->get('summary'),
                $request->get('poster'),
                $request->get('content'),
                $request->get('anonymous')
            );
        }
    }

    /**
     * 删除自己的(回答)文章
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
        return $this->answerService->deleteAnswer($uuid);
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
    public function userAnswer($userUuid)
    {
        return $this->answerService->getUserAnswers($userUuid);
    }
}
