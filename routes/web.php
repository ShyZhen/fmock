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

    Route::get('notification', 'IndexController@notification');
    Route::get('getNotification', 'IndexController@getNotification');






    // 测试控制器
    Route::prefix('test')->group(function () {
        Route::get('/', 'TestController@index');
        Route::get('/index2', 'TestController@index2');
        Route::get('/index3', 'TestController@index3');
        Route::get('/index4', 'TestController@index4');

        Route::get('/sms', 'TestController@sms');
        Route::get('/ipcheck', 'TestController@ipCheck');
        Route::get('/upload', 'TestController@uploadImg');


        Route::get('/oauthGithub/login', 'TestController@oauthGithub');
        Route::get('/oauthGithub/callback', 'TestController@githubCallback');

        #七牛
        Route::get('/qiniu', 'TestController@qiniu');
    });
});
