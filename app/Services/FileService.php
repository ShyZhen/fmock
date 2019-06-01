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
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\UserUploadImageRepository;

class FileService extends Service
{
    private $imageService;

    private $userRepository;

    private $qiniuService;

    private $userUploadImageRepository;

    /**
     * FileService constructor.
     *
     * @param ImageService   $imageService
     * @param UserRepository $userRepository
     * @param QiniuService   $qiniuService
     * @param UserUploadImageRepository   $userUploadImageRepository
     */
    public function __construct(
        ImageService $imageService,
        UserRepository $userRepository,
        QiniuService $qiniuService,
        UserUploadImageRepository $userUploadImageRepository
    ) {
        $this->imageService = $imageService;
        $this->userRepository = $userRepository;
        $this->qiniuService = $qiniuService;
        $this->userUploadImageRepository = $userUploadImageRepository;
    }

    /**
     * 上传图片服务 存入绝对路径 (文章)
     * $path = $file->storeAs($savePath, date('Y-m-d') . '/' . $this->uuid($prefix) . '.' . $fileExt, 'public');
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
            $fileExt = $file->extension();                                                 // $fileExt = 'jpg';
            $tmpPath = $savePath . '/' . date('Y-m-d') . '/';
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
                $this->uploadLog(Auth::id(), $imageUrl);

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
            $fileExt = $file->extension();                                                 // $fileExt = 'jpg';
            $tmpPath = $savePath . '/' . date('Y-m-d') . '/';
            $filePath = '/app/public/' . $tmpPath;                                         // 定义文件的存储路径
            $user = Auth::user();;
            $imageName = $user->uuid . '.' . $fileExt;                                     // 头像名与用户uuid一致
            $storagePath = storage_path($filePath);                                        // 生成系统绝对路径

            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0666, true);
            }
            $fullName = $storagePath . $imageName;

            if ($this->imageService->saveImg($file, $fullName)) {
                $imageUrl = url('/storage/' . $tmpPath . $imageName);
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
            $imageName = self::uuid($prefix) . '.' . $file->extension();
            $fullName = $savePath . '/' . date('Y-m-d') . '/' . $imageName;

            $result = $this->qiniuService->uploadFile($file->path(), $fullName);

            if ($result['code'] === 0) {
                // 由于是覆盖上传,刷新七牛cdn缓存，在文件url后加版本号。(不像头像，这里一般不会重复与覆盖)
                $flushCdn = '?v=' . time();
                $imageUrl = config('filesystems.qiniu.cdnUrl') . '/' . $fullName . $flushCdn;

                // 记录用户上传的文件,便于后台管理
                $this->uploadLog(Auth::id(), $imageUrl);

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

            $fullName = $savePath . '/' . date('Y-m-d') . '/' . $imageName;
            $result = $this->qiniuService->uploadFile($file->path(), $fullName);

            if ($result['code'] === 0) {
                // 由于是覆盖上传,刷新七牛cdn缓存，在文件url后加版本号。
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
     * 上传记录
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
            'url' => $imageUrl
        ]);
    }
}
