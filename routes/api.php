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
    Route::post('user-check', 'AuthController@getAccountStatus');
    Route::post('login', 'AuthController@login');
    Route::post('register-code', 'AuthController@registerCode');
    Route::post('register', 'AuthController@register');
    Route::post('password-code', 'AuthController@passwordCode');
    Route::post('password', 'AuthController@password');

    // OAuth 第三方登录与绑定
    Route::get('oauth/github/login', 'OAuthController@githubLogin');
    Route::get('oauth/github/callback', 'OAuthController@githubCallback');
    Route::post('oauth/wechat/login', 'OAuthController@wechatLogin');

    // 首页文章列表
    Route::get('posts', 'PostController@getAllPosts');

    // 第三方回调
    Route::prefix('callback')->group(function () {
        // 七牛转码
        Route::post('qiniu', 'CallbackController@qiniu');
    });

    #################################↓↓干饭组相关↓↓###################################
    // duomai
    Route::prefix('duomai')->group(function () {
        Route::post('list/{platform}', 'DuomaiController@getList');
        Route::post('search/{platform}', 'DuomaiController@getQueryList');
        Route::post('detail/{platform}', 'DuomaiController@getDetail');
        Route::post('link', 'DuomaiController@getLink');
        Route::post('custom', 'DuomaiController@getCustomProduct');
        Route::post('html/{platform}', 'DuomaiController@getHtml');
    });

    Route::post('login-code', 'AuthController@quickLoginCode');
    Route::post('login-quick', 'AuthController@quickLogin');

    Route::prefix('timeline')->group(function () {
        Route::get('list/{type}', 'TimelineController@getAllTimelines');
        Route::get('detail/{uuid}', 'TimelineController@getTimelineByUuid');
    });
    #################################↑↑干饭组相关↑↑###################################
});

// need access_token
Route::prefix('V1')->namespace('Api\V1')->middleware(['auth:api'])->group(function () {
    // 用户信息
    Route::get('me', 'AuthController@myInfo');
    Route::post('me', 'AuthController@updateMyInfo');
    Route::get('user/{uuid}', 'AuthController@getUserByUuid');
    Route::post('my-name', 'AuthController@updateMyName');
    Route::get('logout', 'AuthController@logout');

    // 用户对文章操作
    Route::get('post/{uuid}', 'PostController@getPostByUuid');
    Route::post('post', 'PostController@createPost');
    Route::put('post/{uuid}', 'PostController@updatePost');
    Route::delete('post/{uuid}', 'PostController@deletePost');

    // 上传文件
    Route::post('file/image', 'FileController@uploadImage');
    Route::post('file/avatar', 'FileController@uploadAvatar');
    Route::post('file/video', 'FileController@uploadVideo');
    Route::post('file/token/{type}', 'FileController@getUploadToken');

    // 文章 赞、取消赞，踩、取消踩
    Route::post('like/post/{uuid}', 'ActionController@likePost');
    Route::post('dislike/post/{uuid}', 'ActionController@dislikePost');
    Route::get('status/post/{uuid}', 'ActionController@statusPost');

    // 评论 赞、取消赞，踩、取消踩
    Route::post('like/comment/{id}', 'ActionController@likeComment');
    Route::post('dislike/comment/{id}', 'ActionController@dislikeComment');
    Route::get('status/comment/{id}', 'ActionController@statusComment');

    // 回答 赞、取消赞，踩、取消踩
    Route::post('like/answer/{uuid}', 'ActionController@likeAnswer');
    Route::post('dislike/answer/{uuid}', 'ActionController@dislikeAnswer');
    Route::get('status/answer/{uuid}', 'ActionController@statusAnswer');

    // 视频 动作状态查询
    Route::post('like/video/{uuid}', 'ActionController@likeVideo');
    Route::post('dislike/video/{uuid}', 'ActionController@dislikeVideo');
    Route::get('status/video/{uuid}', 'ActionController@statusVideo');

    // 评论
    Route::get('comment/{type}/{postUuid}/{sort?}', 'CommentController@getCommentByPostUuid');
    Route::post('comment', 'CommentController@createComment');
    Route::delete('comment/{uuid}', 'CommentController@deleteComment');

    // 问答 - 回答
    Route::get('answers/{postUuid}/{type?}', 'AnswerController@getAnswerByPostUuid');
    Route::get('answer/detail/{uuid}', 'AnswerController@getAnswerByUuid');
    Route::post('answer', 'AnswerController@createAnswer');
    Route::put('answer/{uuid}', 'AnswerController@updateAnswer');
    Route::delete('answer/{uuid}', 'AnswerController@deleteAnswer');

    // 个人中心 动态
//    Route::get('my/likes', 'ActionController@myLike');                         // 我赞过的所有文章、评论
//    Route::get('my/dislikes', 'ActionController@myDislike');                   // 我踩过的所有文章、评论
    Route::get('user/comments/{userUuid}', 'CommentController@userComment');     // 某用户发布的所有评论(包括自己)
    Route::get('user/posts/{userUuid}', 'PostController@userPost');              // 某用户发布的所有文章(包括自己)
    Route::get('user/answers/{userUuid}', 'AnswerController@userAnswer');        // 某用户发布的所有（回答）文章(包括自己)
    // 关注（收藏、点红心）的文章、回答 入口在个人中心九宫格中
    Route::get('collection/{type}', 'ActionController@getMyCollected');
    Route::post('collection', 'ActionController@collected');
    Route::delete('collection/{type}/{uuid}', 'ActionController@unCollect');

    // 关注、取关某人
    Route::post('follow/{userUuid}', 'UserController@follow');
    Route::get('follow/status/{userUuid}', 'UserController@status');
    Route::get('follows/list/{userUuid}', 'UserController@getFollowsList');
    Route::get('fans/list/{userUuid}', 'UserController@getFansList');

    // 我的关注（与我相关），我关注的朋友发的动态
    Route::get('track/{type}', 'ActionController@getTrack');

    // 视频相关
    Route::prefix('video')->group(function () {
        Route::get('item/{uuid}', 'VideoController@getMyVideoItemByUuid');    // 轮询转码结果(即获取video-item)
        Route::put('item/{uuid}', 'VideoController@updateVideoItem');
        Route::delete('item/{uuid}', 'VideoController@deleteVideoItem');
        Route::post('item', 'FileController@saveVideoItem');                  // 客户端上传完成后，数据入库
    });

    #################################↓↓干饭组相关↓↓###################################
    Route::prefix('timeline')->group(function () {
        Route::post('created', 'TimelineController@createTimeline');
        Route::delete('delete/{uuid}', 'TimelineController@deleteTimeline');
        Route::put('report/{uuid}', 'TimelineController@reportTimeline');
        Route::get('user-timeline/{uuid}', 'TimelineController@userTimeline');
    });
    // 文章 赞、取消赞，踩、取消踩
    Route::post('like/timeline/{uuid}', 'ActionController@likeTimeline');
    Route::post('dislike/timeline/{uuid}', 'ActionController@dislikeTimeline');
    Route::get('status/timeline/{uuid}', 'ActionController@statusTimeline');
    #################################↑↑干饭组相关↑↑###################################
});
