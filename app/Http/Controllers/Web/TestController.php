<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/12/19
 * Time: 15:59
 */

namespace App\Http\Controllers\Web;

use App\Services\BaseService\QiniuService;
use App\Library\ElasticSearch\PostElasticSearch;

class TestController
{
    public function index()
    {
        $arr = [33, 2, 45, 6, 77, 24, 100, 47];

        for ($i = 0; $i < count($arr) - 1; $i++) {
            for ($j = 0; $j < count($arr) - 1 - $i; $j++) {
                if ($arr[$j] > $arr[$j + 1]) {
                    $temp = $arr[$j];
                    $arr[$j] = $arr[$j + 1];
                    $arr[$j + 1] = $temp;
                }
            }
        }

        dd($arr);
    }

    public function qiniu()
    {
        // 要上传文件的本地路径
        $filePath = 'C:\Users\z00455118\Pictures\Saved Pictures\huaixiu.jpg';
        // 上传到七牛后保存的文件名
        $key = 'my-php-logo222222.png';

        $a = new QiniuService();
        dd($a->uploadFile($filePath, $key));
    }

    public function esTest()
    {
        $id = 25;
        $postEs = new PostElasticSearch();

//        dd($postEs->deleteIndex());
//        dd($postEs->getMappings());
//        dd($postEs->getSettings());
//        dd($postEs->getDoc($id));
//        dd($postEs->getDocSource($id));
//        dd($postEs->deleteDoc($id));
//        dd($postEs->updateDoc($id, ['title' => 'test']));
        dd($postEs->search('测试'));
    }
}
