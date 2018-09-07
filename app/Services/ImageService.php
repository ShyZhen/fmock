<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/9/7
 */

namespace App\Services;

use Illuminate\Http\Response;

class ImageService extends Service
{
    public  $percent = 1;

    /**
     * 等比压缩
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     * @param $src
     * @param $name
     * @return mixed
     */
    public function saveImg($src, $name)
    {
        list($width, $height, $type, $attr) = getimagesize($src);
        $imageInfo = [
            'width' => $width,
            'height' => $height,
            'type' => image_type_to_extension($type, false),
            'attr' => $attr
        ];
        $create = 'imagecreatefrom' . $imageInfo['type'];
        $image = $create($src);
        $new_width = $imageInfo['width'] * $this->percent;
        $new_height = $imageInfo['height'] * $this->percent;
        $image_thump = imagecreatetruecolor($new_width,$new_height);
        imagecopyresampled($image_thump,$image,0,0,0,0,$new_width,$new_height,$imageInfo['width'],$imageInfo['height']);
        imagedestroy($image);
        $save = "image".$imageInfo['type'];

        if ($save($image_thump, $name)) {
            return true;
        }

        return false;
    }

}