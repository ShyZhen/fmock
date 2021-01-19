<?php
/**
 * CallbackController
 * @author DELL
 * 2021/1/19 15:47
 **/

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    public function qiniu(Request $request)
    {
        @file_put_contents('/tmp/qiniuCallback-'.date('Y-m-d').'log', json_encode([$request->all()]),FILE_APPEND);
    }
}
