<?php

Route::get('/', function () {
    return 'admin /';
});

// no login
Route::namespace('Admin')->group(function () {
    Route::match(['get', 'post'], 'login', 'AuthController@login');
});

// need login
Route::namespace('Admin')->middleware(['admin.auth'])->group(function () {
    Route::post('logout', 'AuthController@logout');
    Route::match(['get', 'post'], 'password', 'AuthController@password');    // 修改密码

    // header 空页面
    Route::get('dashboard', 'IndexController@dashboard');
    Route::get('users', 'IndexController@users');
    Route::get('posts', 'IndexController@posts');
    Route::get('videos', 'IndexController@videos');
    Route::get('orders', 'IndexController@orders');

    // 用户相关
    Route::post('user/all', 'UserController@getAll');
});
