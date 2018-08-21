<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/8/21
 * Time: 12:34
 */

namespace App\Services;


class FileService extends Service
{
    /**
     * 上传文件服务
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     * @param  $file
     * @param  $savePath
     * @param  string $prefix
     * @return mixed
     */
    public function uploadImg($file, $savePath, $prefix = '')
    {
        if ($file->isValid()) {
            $fileExt= $file->extension();
            $path = $file->storeAs($savePath, date('Y-m-d') . '/' . $this->uuid($prefix) . '.' . $fileExt, 'public');

            return $path;
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