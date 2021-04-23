<?php
/**
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: Response::HTTP_CREATED8/8/25
 * Time: 23:25
 */

namespace App\Services;

use Illuminate\Http\Response;
use App\Services\BaseService\RedisService;

class SecurityCheckService extends Service
{
    public const MSG_SEC_CHECK = 'https://api.weixin.qq.com/wxa/msg_sec_check?';  // 敏感词
    public const IMG_SEC_CHECK = 'https://api.weixin.qq.com/wxa/img_sec_check?';  // 图片
    public const OAUTH_TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/token?';    // 获取access_token

    private static $config;
    private $redisService;

    public function __construct(RedisService $redisService)
    {
        $this->redisService = $redisService;

        if (!self::$config) {
            self::$config = config('oauth.wechat');
        }
    }

    public function stringCheck($content): bool
    {
        $accessToken = $this->getAccessToken();
        $params = [
            'access_token' => $accessToken,
        ];

        $requestParams = $this->urlParams($params);
        $url = self::MSG_SEC_CHECK . $requestParams;

        $result = $this->httpRequest($url, ['content' => $content]);
        if ($result['errcode'] == '0') {
            return true;
        }

        return false;
    }

    public function imgCheck($file): bool
    {
        $accessToken = $this->getAccessToken();
        $params = [
            'access_token' => $accessToken,
        ];
        $requestParams = $this->urlParams($params);
        $url = self::IMG_SEC_CHECK . $requestParams;

        $result = $this->picCheck($url, $file);
        if ($result['errcode'] == '0') {
            return true;
        }

        return false;
    }

    public function getAccessToken()
    {
        $key = 'wechatchecktoken';
        if ($this->redisService->isRedisExists($key)) {
            return $this->redisService->getRedis($key);
        }

        if (!self::$config['app_id'] || !self::$config['app_secret']) {
            return response()->json(
                ['message' => 'Wechat AppID or AppSecret is Null!'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $params = [
            'grant_type' => 'client_credential',
            'appid' => self::$config['app_id'],
            'secret' => self::$config['app_secret'],
        ];

        $request_params = $this->urlParams($params);
        $url = self::OAUTH_TOKEN_URL . $request_params;

        $result = $this->httpRequest($url, [], false);

        if (!is_array($result) || !isset($result['access_token'])) {
            return false;
        } else {
            $this->redisService->setRedis($key, $result['access_token'], 'EX', 7000);

            return $result['access_token'];
        }
    }

    public function urlParams($params)
    {
        $buff = '';
        foreach ($params as $k => $v) {
            if ($k != 'sign') {
                $buff .= $k . '=' . $v . '&';
            }
        }
        $buff = trim($buff, '&');

        return $buff;
    }

    /**
     * @param string $url
     * @param array  $params
     * @param bool   $post
     */
    public function httpRequest($url, $params, $post = true)
    {
        $header = [
            'Content-Type: application/json; charset=utf-8',
        ];

        $ch = curl_init();
        if ($post) {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_UNICODE));
        } elseif (is_array($params) && 0 < count($params)) {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . json_encode($params, JSON_UNESCAPED_UNICODE));
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $data = curl_exec($ch);
        if (curl_error($ch)) {
            trigger_error(curl_error($ch));

            return null;
        }
        curl_close($ch);

        return json_decode($data, true);
    }

    /**
     * 针对laravel file的验证
     *
     * @param $url
     * @param $file
     *
     * @return bool|string
     */
    public function picCheck($url, $file)
    {
        $file_path = $file->getRealPath();
        $file_data = ['media' => new \CURLFile($file_path)];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $file_data);
        $data = curl_exec($ch);

        if (curl_error($ch)) {
            trigger_error(curl_error($ch));

            return null;
        }
        curl_close($ch);

        return json_decode($data, true);
    }
}
