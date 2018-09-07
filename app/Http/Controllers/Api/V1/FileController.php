<?php
/**
 * @Author huaixiu.zhen
 * http://litblc.com
 * User: z00455118
 * Date: 2018/9/4
 * Time: 16:45
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\FileService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    private $fileService;

    /**
     * FileController constructor.
     * @param FileService $fileService
     */
    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * @Author huaixiu.zhen
     * http://litblc.com
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
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
            return $this->fileService->uploadImg($file, 'image', 'post-');
        }
    }
}