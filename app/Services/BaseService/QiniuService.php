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
            $this->auth = new Auth($this->config['AccessKey'], $this->config['SecretKey']);
        }

        if (!$this->uploadMgr) {
            $this->uploadMgr = new UploadManager();
        }
    }

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $file
     * @param $key
     * @param string $bucket
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function uploadFile($file, $key, $bucket = '')
    {
        $bucket = $bucket ?: $this->config['bucket'];
        $token = $this->auth->uploadToken($bucket);

        // 上传文件
        // list($ret, $err) = $this->uploadMgr->putFile($token, $key, $file);

        // 上传二进制流
        list($ret, $err) = $this->uploadMgr->put($token, $key, $file);

        if ($err !== null) {
            return $err;
        } else {
            return $ret;
        }
    }
}
