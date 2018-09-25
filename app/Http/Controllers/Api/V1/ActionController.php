<?php
/**
 * 用户动作相关
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/9/19
 */
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ActionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ActionController extends Controller
{
    private $actionService;

    /**
     * ActionController constructor.
     *
     * @param ActionService $actionService
     */
    public function __construct(ActionService $actionService)
    {
        $this->actionService = $actionService;
    }

    /**
     * 获取我关注的所有文章
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyFollowedPosts()
    {
        return $this->actionService->getMyFollowedPosts();
    }

    /**
     * 关注文章
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function followedPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'uuid' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                Response::HTTP_BAD_REQUEST
            );
        } else {
            return $this->actionService->followPost($request->get('uuid'));
        }
    }
}
