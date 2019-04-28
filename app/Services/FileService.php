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
use App\Repositories\Eloquent\UserRepository;

class FileService extends Service
{
    private $imageService;

    private $userRepository;

    /**
     * FileService constructor.
     *
     * @param ImageService   $imageService
     * @param UserRepository $userRepository
     */
    public function __construct(ImageService $imageService, UserRepository $userRepository)
    {
        $this->imageService = $imageService;
        $this->userRepository = $userRepository;
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
            $fileExt = 'jpg';                                                              // $fileExt = $file->extension();
            $tmpPath = $savePath . '/' . date('Y-m-d') . '/';
            $filePath = '/app/public/' . $tmpPath;                                         // 定义文件的存储路径
            $imageName = self::uuid($prefix) . '.' . $fileExt;                            // 定义唯一文件名
            $storagePath = storage_path($filePath);                                        // 生成系统绝对路径

            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0666, true);
            }
            $fullName = $storagePath . $imageName;

            if ($this->imageService->saveImg($file, $fullName)) {
                return response()->json(
                    ['data' => url('/storage/' . $tmpPath . $imageName)],
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
            $fileExt = 'jpg';                                                              // $fileExt = $file->extension();
            $tmpPath = $savePath . '/' . date('Y-m-d') . '/';
            $filePath = '/app/public/' . $tmpPath;                                         // 定义文件的存储路径
            $user = $this->userRepository->findBy('id', Auth::id());
            $imageName = $user->uuid . '.' . $fileExt;                                     // 头像名与用户uuid一致
            $storagePath = storage_path($filePath);                                        // 生成系统绝对路径

            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0666, true);
            }
            $fullName = $storagePath . $imageName;

            if ($this->imageService->saveImg($file, $fullName)) {
                $this->userRepository->update(['avatar' => '/storage/' . $tmpPath . $imageName], $user->id);

                return response()->json(
                    ['data' => url('/storage/' . $tmpPath . $imageName)],
                    Response::HTTP_OK
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
     * 删除文件
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

    public function uploadAvaToQiniu($file, $savePath)
    {

    }
}
