<?php

/**
 * 记录用户上传的文件
 *
 * Author huaixiu.zhen
 * http://litblc.com
 * User: litblc
 * Date: 2019/05/08
 * Time: 19:50
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserUploadImage extends Model
{
    //
    protected $table = 'user_upload_images';

    protected $fillable = [
        'user_id', 'url',
    ];
}
