<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/21
 * Time: 12:34
 */
namespace App\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Services\BaseService\ImageService;
use App\Services\BaseService\QiniuService;
use App\Services\BaseService\RedisService;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\VideoRepository;
use App\Repositories\Eloquent\UserUploadImageRepository;

class FileService extends Service
{
    private $redisService;

    private $imageService;

    private $userRepository;

    private $qiniuService;

    private $videoRepository;

    private $userUploadImageRepository;

    private $uploadVideoRedisKey = 'upload-video:user:';

    /**
     * FileService constructor.
     *
     * @param RedisService              $redisService
     * @param ImageService              $imageService
     * @param UserRepository            $userRepository
     * @param QiniuService              $qiniuService
     * @param VideoRepository           $videoRepository
     * @param UserUploadImageRepository $userUploadImageRepository
     */
    public function __construct(
        RedisService $redisService,
        ImageService $imageService,
        UserRepository $userRepository,
        QiniuService $qiniuService,
        VideoRepository $videoRepository,
        UserUploadImageRepository $userUploadImageRepository
    ) {
        $this->redisService = $redisService;
        $this->imageService = $imageService;
        $this->userRepository = $userRepository;
        $this->qiniuService = $qiniuService;
        $this->videoRepository = $videoRepository;
        $this->userUploadImageRepository = $userUploadImageRepository;
    }

    /**
     * 上传图片服务 存入绝对路径 (文章)
     * $path = $file->storeAs($savePath, date('Y/m/') . $this->uuid($prefix) . '.' . $fileExt, 'public');
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param  $file
     * @param  $savePath (路径，一般为一个文件夹与prefix对应)
     * @param string $prefix (前缀，一般为avatar或post)
     *
     * @return mixed
     */
    public function uploadImg($file, $savePath, $prefix = '')
    {
        if ($file->isValid()) {
            $userId = Auth::id();

            $fileExt = $file->extension();                                                 // $fileExt = 'jpg';
            $tmpPath = $savePath . '/' . date('Y/m/');
            $filePath = '/app/public/' . $tmpPath;                                         // 定义文件的存储路径
            $imageName = self::uuid($prefix) . '.' . $fileExt;                            // 定义唯一文件名
            $storagePath = storage_path($filePath);                                        // 生成系统绝对路径

            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0666, true);
            }
            $fullName = $storagePath . $imageName;

            if ($this->imageService->saveImg($file, $fullName)) {
                $imageUrl = url('/storage/' . $tmpPath . $imageName);

                // 记录用户上传的文件,便于后台管理
                $this->uploadLog($userId, $imageUrl);

                return response()->json(
                    ['data' => $imageUrl],
                    Response::HTTP_CREATED
                );
            }

            return response()->json(
                ['message' => __('app.unknown') . __('app.error')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } else {
            return response()->json(
                ['message' => __('app.upload_file_valida_fail')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    /**
     * 上传头像 存入相对路径
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $file
     * @param $savePath
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAva($file, $savePath)
    {
        if ($file->isValid()) {
            $user = Auth::user();

            $fileExt = $file->extension();                                                 // $fileExt = 'jpg';
            $tmpPath = $savePath . '/' . date('Y/m/');
            $filePath = '/app/public/' . $tmpPath;                                         // 定义文件的存储路径

            $imageName = $user->uuid . '.' . $fileExt;                                     // 头像名与用户uuid一致
            $storagePath = storage_path($filePath);                                        // 生成系统绝对路径

            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0666, true);
            }
            $fullName = $storagePath . $imageName;

            if ($this->imageService->saveImg($file, $fullName)) {

                // 刷新本地缓存
                $flushCdn = '?v=' . time();

                $imageUrl = url('/storage/' . $tmpPath . $imageName . $flushCdn);
                // 存储绝对路径入库
                $this->userRepository->update(['avatar' => $imageUrl], $user->id);

                return response()->json(
                    ['data' => $imageUrl],
                    Response::HTTP_CREATED
                );
            }

            return response()->json(
                ['message' => __('app.unknown') . __('app.error')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } else {
            return response()->json(
                ['message' => __('app.upload_file_valida_fail')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    /**
     * 本地不支持视频转码、切片等
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $file
     * @param $savePath
     * @param string $prefix
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadVideo($file, $savePath, $prefix = '')
    {
        if ($file->isValid()) {
            $userId = Auth::id();

            $fileExt = $file->extension();                                                 // $fileExt = 'jpg';
            $tmpPath = $savePath . '/' . date('Y/m/');
            $filePath = '/app/public/' . $tmpPath;                                         // 定义文件的存储路径
            $videoName = self::uuid($prefix) . '.' . $fileExt;                            // 定义唯一文件名
            $storagePath = storage_path($filePath);                                        // 生成系统绝对路径

            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0666, true);
            }

            $fullName = $storagePath . $videoName;

            // 频率限制
            if ($this->redisService->isRedisExists($this->uploadVideoRedisKey . $userId)) {
                return response()->json(
                    ['message' => __('app.action_ttl') . $this->redisService->getRedisTtl($this->uploadVideoRedisKey . $userId) . 's'],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            // 上传操作
            if ($this->imageService->saveVideo($file, $fullName)) {

                // 添加频率限制key
                $this->redisService->setRedis($this->uploadVideoRedisKey . $userId, 'create', 'EX', 90);

                $videoUrl = url('/storage/' . $tmpPath . $videoName);
                $videoHlsUrl = '';

                // 保存数据入库
                $video = $this->saveVideo($videoUrl, $videoHlsUrl);
                if ($video) {
                    return response()->json(
                        ['data' => $video->uuid],
                        Response::HTTP_CREATED
                    );
                } else {
                    return response()->json(
                        ['message' => __('app.try_again')],
                        Response::HTTP_INTERNAL_SERVER_ERROR
                    );
                }
            }

            return response()->json(
                ['message' => __('app.unknown') . __('app.error')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } else {
            return response()->json(
                ['message' => __('app.upload_file_valida_fail')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    /**
     * 删除本地文件
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $filePath
     *
     * @return bool
     */
    public function deleteImg($filePath)
    {
        $res = unlink($filePath);

        return $res;
    }

    /**
     * 上传图片到七牛服务
     * 直接返回url，跟内容一并保存
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $file
     * @param $savePath
     * @param string $prefix
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImgToQiniu($file, $savePath, $prefix = '')
    {
        if ($file->isValid()) {
            $userId = Auth::id();

            $imageName = self::uuid($prefix) . '.' . $file->extension();
            $fullName = $savePath . '/' . date('Y/m/') . $imageName;

            $result = $this->qiniuService->uploadFile($file->path(), $fullName);

            if ($result['code'] === 0) {

                // 七牛设置的图片样式（加水印等其他操作）
                $imageProcess = '_fmock';
                $imageUrl = config('filesystems.qiniu.cdnUrl') . '/' . $fullName . $imageProcess;

                // 记录用户上传的文件,便于后台管理
                $this->uploadLog($userId, $imageUrl);

                return response()->json(
                    ['data' => $imageUrl],
                    Response::HTTP_CREATED
                );
            }

            return response()->json(
                ['message' => __('app.upload_file_qiniu_fail')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } else {
            return response()->json(
                ['message' => __('app.upload_file_valida_fail')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    /**
     * 上传头像 存入七牛服务
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $file
     * @param $savePath
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAvaToQiniu($file, $savePath)
    {
        if ($file->isValid()) {
            $user = Auth::user();

            // 头像名与用户uuid一致
            $imageName = $user->uuid . '.' . $file->extension();

            $fullName = $savePath . '/' . date('Y/m/') . $imageName;
            $result = $this->qiniuService->uploadFile($file->path(), $fullName);

            if ($result['code'] === 0) {

                // 覆盖上传,刷新七牛cdn缓存及时更新
                $flushCdn = '?v=' . time();
                $imageUrl = config('filesystems.qiniu.cdnUrl') . '/' . $fullName . $flushCdn;

                // 存储绝对路径入库
                $this->userRepository->update(['avatar' => $imageUrl], $user->id);

                return response()->json(
                    ['data' => $imageUrl],
                    Response::HTTP_CREATED
                );
            }

            return response()->json(
                ['message' => __('app.upload_file_qiniu_fail')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } else {
            return response()->json(
                ['message' => __('app.upload_file_valida_fail')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    /**
     * 上传视频到七牛 切片
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $file
     * @param $savePath
     * @param string $prefix
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadVideoToQiniu($file, $savePath, $prefix = '')
    {
        if ($file->isValid()) {
            $userId = Auth::id();

            $videoName = self::uuid($prefix) . '.' . $file->extension();
            $fullName = $savePath . '/' . date('Y/m/') . $videoName;

            // 频率限制
            if ($this->redisService->isRedisExists($this->uploadVideoRedisKey . $userId)) {
                return response()->json(
                    ['message' => __('app.action_ttl') . $this->redisService->getRedisTtl($this->uploadVideoRedisKey . $userId) . 's'],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $result = $this->qiniuService->uploadVideo($file->path(), $fullName);

            if ($result['code'] === 0) {

                // 添加频率限制key
                $this->redisService->setRedis($this->uploadVideoRedisKey . $userId, 'create', 'EX', 90);

                $videoUrl = config('filesystems.qiniu.cdnUrlVideo') . '/' . $result['data']['key'];
                $videoHlsUrl = config('filesystems.qiniu.cdnUrlVideo') . '/' . $result['m3u8'];

                // 保存数据入库
                $video = $this->saveVideo($videoUrl, $videoHlsUrl);

                if ($video) {
                    return response()->json(
                        ['data' => $video->uuid],
                        Response::HTTP_CREATED
                    );
                } else {
                    return response()->json(
                        ['message' => __('app.try_again')],
                        Response::HTTP_INTERNAL_SERVER_ERROR
                    );
                }
            }

            return response()->json(
                ['message' => __('app.upload_file_qiniu_fail')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } else {
            return response()->json(
                ['message' => __('app.upload_file_valida_fail')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    /**
     * 图片上传记录
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $userId
     * @param $imageUrl
     */
    private function uploadLog($userId, $imageUrl)
    {
        $this->userUploadImageRepository->create([
            'user_id' => $userId,
            'url' => $imageUrl,
        ]);
    }

    /**
     * 保存上传的视频 url 入库
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $videoUrl
     * @param $videoHlsUrl
     *
     * @return mixed
     */
    private function saveVideo($videoUrl, $videoHlsUrl)
    {
        $video = $this->videoRepository->create([
            'uuid' => self::uuid('video-'),  // 禁止使用同样的uuid，防止被人猜到暴露
            'user_id' => Auth::id(),
            'url' => $videoUrl,
            'hls_url' => $videoHlsUrl,
        ]);

        return $video;
    }
}
