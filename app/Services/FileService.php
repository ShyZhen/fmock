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

class FileService extends Service
{
    private $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * 上传文件服务
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     * @param  $file
     * @param  $savePath(路径，一般为一个文件夹与prefix对应)
     * @param  string $prefix(前缀，一般为avatar或post)
     * @return mixed
     */
    public function uploadImg($file, $savePath, $prefix = '')
    {

        if ($file->isValid()) {
            $fileExt= $file->extension();
            $tmpPath = $savePath . '/' . date('Y-m-d') . '/';
            $filePath = '/app/public/' . $tmpPath;            // 定义文件的存储路径
            $imageName = $this->uuid($prefix) . '.' . $fileExt;                            // 定义唯一文件名
            $storagePath = storage_path($filePath);                                        // 生成系统绝对路径
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0777, true);
            }
            $fullName = $storagePath . $imageName;
            if ($this->imageService->saveImg($file, $fullName)) {
                return response()->json(
                    ['data' => url('/storage/' . $tmpPath . $imageName)],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            //$path = $file->storeAs($savePath, date('Y-m-d') . '/' . $this->uuid($prefix) . '.' . $fileExt, 'public');
        } else {
            return response()->json(
                ['message' => '文件无效'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    /**
     * 删除文件
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     * @param $filePath
     * @return bool
     */
    public function deleteImg($filePath)
    {
        $res = unlink($filePath);
        return $res;
    }
}