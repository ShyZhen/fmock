<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/12/19
 * Time: 15:59
 */
namespace App\Http\Controllers\Web;

use GuzzleHttp\Client;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestController
{
    public function index()
    {
//        dd($_SERVER);
        $arr = [33, 2, 45, 6, 77, 24, 100, 47];

        for ($i = 0; $i < count($arr) - 1; $i++) {
            for ($j = 0; $j < count($arr) - 1 - $i; $j ++) {
                if ($arr[$j] > $arr[$j + 1]) {
                    $temp = $arr[$j];
                    $arr[$j] = $arr[$j + 1];
                    $arr[$j + 1] = $temp;
                }
            }
        }

        dd($arr);
    }

    public function index2()
    {
        // 一次请求都没结束，PHP还没返回值给浏览器呢，怎么可能取出来
        setcookie('a', 'value');
        print_r($_COOKIE['a']);
    }

    public function index3()
    {
        $i = 5;
        echo $i++ . $i++ . $i++;
    }

    public function index4()
    {
//        session('a', 'ewerwerwer');
        dd(session('a'));
    }

    public function sms()
    {
        $jsonParam = [
            'code' => '212312'
        ];
        $res = SmsService::sendSms('16625200528', json_encode($jsonParam), 'FMock');

        if ($res['Code'] === 'OK') {
            print_r('OK');
            print_r($res);
        } else {
            print_r('error');
            print_r($res);
        }
    }

    public function ipCheck()
    {
        Request::setTrustedProxies([getenv('SERVER_ADDR')]);
        return Request()->getClientIp();


        $result = ['Code' => 'NO'];
        $request::setTrustedProxies([getenv('SERVER_ADDR')]);
        print_r($request->getClientIp());
    }

    public function oauthGithub()
    {
        $client = new Client();
        $data = [
            'client_id' => env('GithubClientID'),
            'redirect_uri' => 'http://192.168.204.112:82/test/oauthGithub/callback'
        ];
        $res = $client->request('GET', 'https://github.com/login/oauth/authorize?client_id='.env('GithubClientID').'&redirect_uri=http://192.168.204.112:82/test/oauthGithub/callback');
        echo $res->getBody();


    }
    public function githubCallback()
    {
//        dd(env('GithubClientID'));
        dd(env('GithubClientSecret'));

    }

    public function qiniu()
    {
        dd(config('filesystems.qiniu'));
    }

}
