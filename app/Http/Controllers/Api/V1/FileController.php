<?php
/**
 * 上传文件类
 *
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/9/4
 * Time: 16:45
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Services\FileService;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    private $fileService;

    /**
     * FileController constructor.
     *
     * @param FileService $fileService
     */
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * 上传图片
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png,gif|between:1,5000',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                Response::HTTP_BAD_REQUEST
            );
        } else {
            $file = $request->file('image');

            if (env('QiniuService')) {

                // 上传图片到七牛
                $res = $this->fileService->uploadImgToQiniu($file, 'image', 'post-');
            } else {
                // 上传图片到本地
                $res = $this->fileService->uploadImg($file, 'image', 'post-');
            }

            return $res;
        }
    }

    /**
     * 上传头像 需要在前端进行处理
     * 压缩并resize后调用该api
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpg,jpeg,png,gif|between:1,1000',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                Response::HTTP_BAD_REQUEST
            );
        } else {
            $file = $request->file('avatar');

            if (env('QiniuService')) {

                // 上传图片到七牛
                $res = $this->fileService->uploadAvaToQiniu($file, 'avatar');
            } else {
                // 上传图片到本地
                $res = $this->fileService->uploadAva($file, 'avatar');
            }

            return $res;
        }
    }

    /**
     * 上传视频 限制最大500M
     *
     * @Author huaixiu.zhen
     * http://litblc.com
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadVideo(Request $request)
    {
        $mimeTypes = 'video/avi,video/mpeg,video/quicktime,video/x-flv,video/mp4,application/x-mpegURL,video/3gpp,video/x-msvideo,video/x-ms-wmv,video/MP2T';
        $validator = Validator::make($request->all(), [
            'video' => 'required|mimetypes:' . $mimeTypes . '|max:512000',
        ]);

        if ($validator->fails()) {
            return response()->json(
                ['message' => $validator->errors()->first()],
                Response::HTTP_BAD_REQUEST
            );
        } else {
            $file = $request->file('video');

            if (env('QiniuService')) {

                // 上传视频到七牛
                // $res = $this->fileService->uploadVideoToQiniu($file, 'video', 'video-');  // 主动
                $res = $this->fileService->uploadVideoToQiniuNew($file, 'video', 'video-');  // 异步工作流
            } else {
                // 上传视频到本地
                $res = $this->fileService->uploadVideo($file, 'video', 'video-');
            }

            return $res;
        }
    }
}
