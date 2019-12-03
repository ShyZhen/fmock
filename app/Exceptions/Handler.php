<?php

namespace App\Exceptions;

use Exception;
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
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $exception
     *
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $exception
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if (!env('APP_DEBUG')) {
            return self::handler($request, $exception);
        }

        return parent::render($request, $exception);
    }

    public function handler($request, Exception $exception)
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
