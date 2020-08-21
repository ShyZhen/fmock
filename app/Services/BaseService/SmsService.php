<?php
/**
 * 阿里发送短信服务
 *
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: 2019/4/19
 * Time: 20:39
 */

namespace App\Services\BaseService;

use App\Services\Service;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class SmsService extends Service
{
    /**
     * 发送短信
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param string      $phoneNumber
     * @param string-json $param        "{'code':'56444522'}"
     * @param string      $signName
     * @param string      $templateCode
     *
     * @throws ClientException
     *
     * @return array
     */
    public static function sendSms($phoneNumber, $param, $signName = '', $templateCode = '')
    {
        $signName = $signName ?: env('SignName');
        $templateCode = $templateCode ?: env('TemplateCode');

        AlibabaCloud::accessKeyClient(env('AccessKeyID'), env('AccessKeySecret'))
            ->regionId('cn-shenzhen')
            ->asDefaultClient();

        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->host('dysmsapi.aliyuncs.com')
                ->action('SendSms')
                ->method('POST')
                ->options([
                    'query' => [
                        'PhoneNumbers' => $phoneNumber,
                        'SignName' => $signName,
                        'TemplateCode' => $templateCode,
                        'TemplateParam' => $param,
                    ],
                ])
                ->request();

            return $result->toArray();
        } catch (ClientException $e) {
            return $e->getErrorMessage() . PHP_EOL;
        } catch (ServerException $e) {
            return $e->getErrorMessage() . PHP_EOL;
        }
    }
}
