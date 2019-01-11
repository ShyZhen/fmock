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

use App\Http\Controllers\Controller;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    private $postService;

    /**
     * @param PostService $postService
     */
    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * 首页文章列表
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function getAllPosts(Request $request)
    {
        $sort = $request->get('sort', 'post-new');

        return $this->postService->getAllPosts($sort);
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
    public function getPostByUuid($uuid)
    {
        return $this->postService->getPostByUuid($uuid);
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
    public function createPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:64',
            'content' => 'required|max:'.env('CONTENT_NUM'),
            'anonymous' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->postService->createPost(
                $request->get('title'),
                $request->get('content'),
                $request->get('anonymous')
            );
        }
    }

    /**
     * 更新自己的文章
     *
     * @Author huaixiu.zhen@gmail.com
     * http://litblc.com
     *
     * @param Request $request
     * @param $uuid
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePost(Request $request, $uuid)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|max:'.env('CONTENT_NUM'),
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->postService->updatePost(
                $uuid,
                $request->get('content')
            );
        }
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
    public function deletePost($uuid)
    {
        return $this->postService->deletePost($uuid);
    }
}
