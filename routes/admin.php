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
    Route::get('users', 'UserController@list');
    Route::get('dashboard', 'IndexController@dashboard');

});
