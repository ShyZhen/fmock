<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/', function () {
    return ('api /');
});

// no access_token
Route::prefix('V1')->namespace('Api\V1')->middleware('cors')->group(function() {
    Route::get('locale', 'IndexController@getLocale');
    Route::post('login', 'AuthController@login');
    Route::post('register-code', 'AuthController@registerCode');
    Route::post('register', 'AuthController@register');
    Route::post('password-code', 'AuthController@passwordCode');
    Route::post('password', 'AuthController@password');

    Route::get('posts', 'PostController@getAllPosts');
    Route::post('comment', 'CommentController@create');
    Route::get('comment', 'CommentController@comments');
});


// need access_token
Route::prefix('V1')->namespace('Api\V1')->middleware(['cors', 'auth:api'])->group(function() {
    Route::get('me', 'AuthController@myInfo');
    Route::get('logout', 'AuthController@logout');

    Route::get('post/{uuid}', 'PostController@getPostById');
    Route::post('post', 'PostController@createPost');
    Route::put('post/{uuid}', 'PostController@updatePost');
    Route::delete('post/{uuid}', 'PostController@deletePost');
});