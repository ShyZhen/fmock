<?php
/**
 * 评论控制器
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/12/04
 * Time: 20:32
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Services\CommentService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    private $commentService;

    // 评论的主体 是文章还是回答
    private $type = ['post', 'answer', 'video', 'timeline'];

    /**
     * CommentController constructor.
     *
     * @param CommentService $commentService
     */
    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * 获取某篇文章的评论
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $type
     * @param $postUuid
     * @param string $sort = ['new', hot]
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCommentByPostUuid($type, $postUuid, $sort = 'new')
    {
        if (in_array($type, $this->type)) {
            return $this->commentService->getAllComments($type, $postUuid, $sort);
        } else {
            return response()->json(
                ['message' => __('app.normal_param_err')],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * 写评论/回复评论
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'resource_uuid' => 'required',
            'parent_id' => 'required|integer',       // 父级评论id
            'content' => 'required|max:500',
            'type' => [
                'required',
                Rule::in($this->type),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->commentService->createComment(
                $request->get('resource_uuid'),
                $request->get('parent_id'),
                $request->get('content'),
                $request->get('type')
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
        return $this->commentService->deleteComment($id);
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
    public function userComment($userUuid)
    {
        return $this->commentService->getUserComments($userUuid);
    }
}
