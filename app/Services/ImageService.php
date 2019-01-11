<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/9/7
 */
namespace App\Services;

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
        // 同类型压缩 $save = "image" . $imageInfo['type'];
        $save = 'imagejpeg';

        if ($save($newThump, $name)) {
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
    public function setPercent($maxWidth, $width)
    {
        if ($width > $maxWidth) {
            $percent = $maxWidth / $width;
        } else {
            $percent = 1;
        }

        return $percent;
    }
}
