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
    return 'FMock Api /';
});

// no access_token
Route::prefix('V1')->namespace('Api\V1')->group(function () {
    // 用户登录注册
    Route::get('locale', 'IndexController@getLocale');
    Route::post('login', 'AuthController@login');
    Route::post('register-code', 'AuthController@registerCode');
    Route::post('register', 'AuthController@register');
    Route::post('password-code', 'AuthController@passwordCode');
    Route::post('password', 'AuthController@password');

    // 首页文章列表
    Route::get('posts', 'PostController@getAllPosts');
});

// need access_token
Route::prefix('V1')->namespace('Api\V1')->middleware(['auth:api'])->group(function () {
    // 用户信息
    Route::get('me', 'AuthController@myInfo');
    Route::post('me', 'AuthController@updateMyInfo');
    Route::get('user/{uuid}', 'AuthController@getUserByUuid');
    Route::post('my-name', 'AuthController@updateMyName');
    Route::get('logout', 'AuthController@logout');

    // 文章
    Route::get('post/{uuid}', 'PostController@getPostByUuid');
    Route::post('post', 'PostController@createPost');
    Route::put('post/{uuid}', 'PostController@updatePost');
    Route::delete('post/{uuid}', 'PostController@deletePost');

    // 上传文件
    Route::post('file/image', 'FileController@uploadImage');
    Route::post('file/avatar', 'FileController@uploadAvatar');

    // 关注
    Route::get('follow/posts', 'ActionController@getMyFollowedPosts');
    Route::post('follow/post', 'ActionController@followedPost');
    Route::delete('follow/post/{uuid}', 'ActionController@unFollow');

    // 文章 赞、取消赞，踩、取消踩
    Route::get('like/post/{uuid}', 'ActionController@likePost');
    Route::get('dislike/post/{uuid}', 'ActionController@dislikePost');
    Route::get('status/post/{uuid}', 'ActionController@statusPost');

    // 评论
    Route::get('comment/{postUuid}/{type?}', 'CommentController@getCommentByPostUuid');
    Route::post('comment', 'CommentController@createComment');
    Route::delete('comment/{uuid}', 'CommentController@deleteComment');

    // 评论 赞、取消赞，踩、取消踩
    Route::get('like/comment/{id}', 'ActionController@likeComment');
    Route::get('dislike/comment/{id}', 'ActionController@dislikeComment');
    Route::get('status/comment/{id}', 'ActionController@statusComment');
});
