<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return 'web /';
});

Route::namespace('Web')->group(function () {
    Route::get('index', 'IndexController@index');

    Route::get('event', 'IndexController@event');
    Route::get('rabbitmq', 'IndexController@rabbitmqPublish');

    Route::get('notification', 'IndexController@notification');
    Route::get('getNotification', 'IndexController@getNotification');

    // 测试控制器
    Route::prefix('test')->group(function () {
        Route::get('/', 'TestController@index');

        #七牛测试
        Route::get('/qiniu', 'TestController@qiniu');

        # ES测试
        Route::get('/es', 'TestController@esTest');
    });
});
