<?php
/**
 * 上传图片到本地服务器
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/9/7
 */

namespace App\Services\BaseService;

use App\Services\Service;

class ImageService extends Service
{
    public $percent = 1;

    private $maxWidth = 1080;

    /**
     * 等比压缩并保存
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $file
     * @param $name
     *
     * @return mixed
     */
    public function saveImg($file, $name)
    {
        list($width, $height, $type, $attr) = getimagesize($file);
        $imageInfo = [
            'width' => $width,
            'height' => $height,
            'type' => image_type_to_extension($type, false),
            'attr' => $attr,
        ];
        $this->percent = $this->setPercent($this->maxWidth, $imageInfo['width']);
        $create = 'imagecreatefrom' . $imageInfo['type'];
        $image = $create($file);
        $newWidth = $imageInfo['width'] * $this->percent;
        $newHeight = $imageInfo['height'] * $this->percent;
        $newThump = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newThump, $image, 0, 0, 0, 0, $newWidth, $newHeight, $imageInfo['width'], $imageInfo['height']);
        imagedestroy($image);

        // 同类型压缩
        $save = 'image' . $imageInfo['type'];
        // 统一压缩生成jpg格式
        // $save = 'imagejpeg';

        if ($save($newThump, $name)) {
            return true;
        }

        return false;
    }

    /**
     * 推荐视频一律上传至七牛压缩切片
     *
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $file
     * @param $name
     *
     * @return bool
     */
    public function saveVideo($file, $name)
    {
        if (move_uploaded_file($file, $name)) {
            return true;
        }

        return false;
    }

    /**
     * 设置缩放比例
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param $maxWidth
     * @param $width
     *
     * @return float|int
     */
    private function setPercent($maxWidth, $width)
    {
        if ($width > $maxWidth) {
            $percent = $maxWidth / $width;
        } else {
            $percent = 1;
        }

        return $percent;
    }
}
