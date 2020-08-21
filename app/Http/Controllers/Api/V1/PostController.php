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
use App\Services\PostService;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    private $type = ['share', 'question', 'dynamite', 'friend', 'recruit'];

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
        $type = $request->get('type', 'all');

        if (in_array($type, array_merge($this->type, ['hot', 'all']))) {
            return $this->postService->getAllPosts($type);
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
            'summary' => 'present|max:80',
            'poster' => 'present|max:128',
            'content' => 'required|max:' . env('CONTENT_NUM'),
            'anonymous' => 'required|boolean',
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
            return $this->postService->createPost(
                $request->get('title'),
                $request->get('summary'),
                $request->get('poster'),
                $request->get('content'),
                $request->get('anonymous'),
                $request->get('type')
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
            'summary' => 'present|max:80',
            'poster' => 'present|max:128',
            'content' => 'required|max:' . env('CONTENT_NUM'),
            'anonymous' => 'required|boolean',
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
            return $this->postService->updatePost(
                $uuid,
                $request->get('summary'),
                $request->get('poster'),
                $request->get('content'),
                $request->get('anonymous'),
                $request->get('type')
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
    public function userPost($userUuid)
    {
        return $this->postService->getUserPosts($userUuid);
    }
}
