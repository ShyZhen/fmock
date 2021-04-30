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
     * 上传文件（这里主要用来上传图片）
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $filePath       // 文件路径、tmp_name
     * @param $key            // 生成的文件名
     * @param string $bucket // 空间名
     *
     * @throws \Exception
     *
     * @return mixed
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
                'data' => $err,
            ];
        } else {
            $res = [
                'code' => 0,
                'data' => $ret,
            ];
        }

        return $res;
    }

    /**
     * 上传视频到七牛 切片、水印等操作(主动)
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $filePath
     * @param $key
     * @param string $bucket
     *
     * @throws \Exception
     *
     * @return array
     */
    public function uploadVideo($filePath, $key, $bucket = '')
    {
        $bucket = $bucket ?: $this->config['bucketVideo'];

        $hlsName = $this->hlsM3u8Name($key);
        $fops = $this->m3u8Fops() . $this->videoWatermark();

        $saveasKey = \Qiniu\base64_urlSafeEncode($bucket . ':' . $hlsName);
        $fops .= '|saveas/' . $saveasKey;

        $policy = [
            'persistentOps' => $fops,
            'persistentPipeline' => $this->config['videoPipeline'],
        ];
        $token = $this->auth->uploadToken($bucket, $key, 3600, $policy);

        // 上传文件
        list($ret, $err) = $this->uploadMgr->putFile($token, $key, $filePath);

        if ($err !== null) {
            $res = [
                'code' => -1,
                'data' => $err,
            ];
        } else {
            $res = [
                'code' => 0,
                'data' => $ret,
                'm3u8' => $hlsName,
            ];
        }

        return $res;
    }

    /**
     * @param $isVideo
     * @param null $key
     *
     * @return string
     */
    public function getUploadToken($isVideo, $key = null)
    {
        $bucket = $isVideo ? $this->config['bucketVideo'] : $this->config['bucket'];

        return $this->auth->uploadToken($bucket, $key);
    }

    /**
     * 生成视频某时间的缩略图
     * 直接拼在url后即可
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $offset
     *
     * @return string
     */
    public function videoVframe($offset)
    {
        return '?vframe/jpg/offset/' . $offset;
    }

    /**
     * 统一m3u8文件路径以及命名规则
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $key
     *
     * @return string
     */
    private function hlsM3u8Name($key)
    {
        return 'hls/' . date('Y/m/') . md5($key) . '.m3u8';
    }

    /**
     * 七牛视频切片配置
     * 上线前需要更新为合适的配置
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return string
     */
    private function m3u8Fops()
    {
        return $this->config['m3u8Fops'];
    }

    /**
     * 视频水印参数信息
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @return string
     */
    private function videoWatermark()
    {
        $img = $this->config['watermarkImg'];
        $text = '@' . \Illuminate\Support\Facades\Auth::user()->name;

        $watermarkImg = '/wmImage/' . \Qiniu\base64_urlSafeEncode($img) . '/wmOffsetX/-10/wmOffsetY/-8';
        $watermarkText = '/wmText/' . \Qiniu\base64_urlSafeEncode($text) .
            '/wmGravityText/SouthEast/wmFontColor/' . \Qiniu\base64_urlSafeEncode('#0000002e') .
            '/wmFontSize/12';

        return $watermarkImg . $watermarkText;
    }
}
