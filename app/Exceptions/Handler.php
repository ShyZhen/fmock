<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Response;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     *
     * @throws \Exception
     *
     * @return void
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * 生成环境报错信息处理
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable               $exception
     *
     * @throws \Throwable
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        if (!env('APP_DEBUG')) {
            return self::handler($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * author shyZhen <huaixiu.zhen@gmail.com>
     * https://www.litblc.com
     *
     * @param $request
     * @param Throwable $exception
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handler($request, Throwable $exception)
    {
//        记录日志
//        echo date('Y-m-d H:i:s');
//        print_r($exception->getMessage());
//        print_r($exception->getLine());
//        print_r($exception->getFile());

        return response()->json(
            ['message' => $exception->getMessage()],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
