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
use App\Services\CommentService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    private $commentService;

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
     * @param $postUuid
     * @param string $type = ['new', hot]
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCommentByPostUuid($postUuid, $type = 'new')
    {
        return $this->commentService->getAllComments($postUuid, $type);
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
            'post_uuid' => 'required',
            'parent_id' => 'required',       // 父级评论id
            'content' => 'required|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->commentService->createComment(
                $request->get('post_uuid'),
                $request->get('parent_id'),
                $request->get('content')
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
}
