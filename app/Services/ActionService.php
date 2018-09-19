<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 */
namespace App\Services;

use App\Repositories\Eloquent\UserRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ActionService extends Service
{
    private $userRepository;

    /**
     * AuthService constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyFollowedPosts()
    {
        $posts = Auth::user()->myFollowedPosts()->paginate(env('PER_PAGE', 10));

        if ($posts->count()) {
            foreach ($posts as $post) {
                $post->userinfo = $this->userRepository->getUserInfoById($post->user_id);
            }
        }

        return response()->json(
            ['data' => $posts],
            Response::HTTP_OK
        );
    }


}
