<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2019/4/23
 * Time: 19:48
 */
namespace App\Services\OAuthService;

use App\Models\User;
use GuzzleHttp\Client;
use App\Services\Service;
use Illuminate\Http\Response;

class GithubService extends Service
{
    /**
     * 前端重定向地址
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function githubLogin()
    {
        $clientId = env('GithubClientID');
        if (!$clientId) {
            return response()->json(
                ['message' => 'Github ClientID is Null!'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $baseUri = 'https://github.com/login/oauth/authorize';
        $redirectUri = 'http://192.168.204.112:82/api/V1/oauth/github/callback';
        $state = self::uuid('code-');
        $authorizeUri = $baseUri . '?client_id=' . $clientId . '&redirect_uri=' . $redirectUri . '&state=' . $state . '&scope=user';

        // 前端重定向地址
        return response()->json(
            ['redirectUrl' => $authorizeUri],
            Response::HTTP_OK
        );
    }

    /**
     * 授权登录后的回调
     * 使用第三方登录成功后，判断是否有用户有该GitHub_id，若有则生成token返回，无则创建用户生成token返回
     * 之后判断是否曾经绑定过邮箱或者手机号，有则binding_status返回true
     * 若binding_status为false,则弹出提示绑定手机或者邮箱（类似注册页面，逻辑类似）
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function githubCallback($code)
    {
        $accessTokenUri = 'https://github.com/login/oauth/access_token';
        $data = [
            'form_params' => [
                'client_id' => env('GithubClientID'),
                'client_secret' => env('GithubClientSecret'),
                'code' => $code,
            ],
        ];
        $client = new Client();
        $response = $client->post($accessTokenUri, $data);
        $temp = $response->getBody()->getContents();
        parse_str($temp, $result);

        if (array_key_exists('access_token', $result)) {
            $githubUserInfo = self::getGithubUserInfo($result['access_token']);

            $binding = false;
            $user = User::where('github_id', $githubUserInfo->id)->first();

            // 已经使用GitHub登录过,但是可能没绑定自己的账号
            if ($user && $user->count()) {

                // 判断是否冻结用户
                if ($user->closure == 'none') {
                    $token = $user->createToken(env('APP_NAME'))->accessToken;

                    // 已经完成绑定逻辑
                    if ($user->email || $user->mobile) {
                        $binding = true;
                    }
                } else {
                    return json_encode(
                        ['message' => __('app.user_is_closure')]
//                        Response::HTTP_BAD_REQUEST
                    );
                }
            } else {
                // 根据GitHub创建用户
                $uuid = self::uuid('user-');
                $user = User::create([
                    'name' => self::uuid('github-' . $githubUserInfo->name),
                    'password' => bcrypt(''),
                    'uuid' => $uuid,
                    'github_id' => $githubUserInfo->id,
                    'github' => $githubUserInfo->login,
                    'avatar' => $githubUserInfo->avatar_url,
                ]);
                $token = $user->createToken(env('APP_NAME'))->accessToken;
            }

            return json_encode(
                ['access_token' => $token, 'binding_status' => $binding]
//                Response::HTTP_OK
            );
        } else {
            return json_encode(
                ['message' => __('app.token_error')]
//                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * 获取GitHub用户信息接口
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $accessToken
     *
     * @return mixed
     */
    private static function getGithubUserInfo($accessToken)
    {
        $userUri = 'https://api.github.com/user';
        $data = [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ];

        $client = new Client();
        $response = $client->get($userUri, $data);

        return json_decode($response->getBody()->getContents());
    }
}
