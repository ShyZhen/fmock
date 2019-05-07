<?php
/**
 * 七牛服务
 *
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: 2019/5/6
 * Time: 20:39
 */
namespace App\Services\BaseService;

use Qiniu\Auth;
use App\Services\Service;
use Qiniu\Storage\UploadManager;

class QiniuService extends Service
{
    private $config;

    private $auth;

    private $uploadMgr;

    /**
     * QiniuService constructor.
     */
    public function __construct()
    {
        if (!$this->auth) {
            $this->config = config('filesystems.qiniu');
            $this->auth = new Auth($this->config['accessKey'], $this->config['secretKey']);
        }

        if (!$this->uploadMgr) {
            $this->uploadMgr = new UploadManager();
        }
    }

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $filePath       // 文件路径、tmp_name
     * @param $key            // 生成的文件名
     * @param string $bucket  // 空间名
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function uploadFile($filePath, $key, $bucket = '')
    {
        $bucket = $bucket ?: $this->config['bucket'];
        $token = $this->auth->uploadToken($bucket, $key);

        // 上传文件
         list($ret, $err) = $this->uploadMgr->putFile($token, $key, $filePath);

        if ($err !== null) {
            $res = [
                'code' => -1,
                'data' => $err
            ];
        } else {
            $res = [
                'code' => 0,
                'data' => $ret
            ];
        }

        return $res;
    }
}
