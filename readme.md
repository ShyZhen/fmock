<p align="center"><img src="http://m.fmock.com/static/img/FMOCK-LOGO.png"></p>
<p align="center">
	<a href="https://github.styleci.io/repos/145133991">
        <img src="https://github.styleci.io/repos/145133991/shield" alt="StyleCI">
    </a>
    <a href="https://travis-ci.org/ShyZhen/fmock">
        <img src="https://travis-ci.org/ShyZhen/fmock.svg?branch=master" alt="Build Status">
    </a>
    <a href="#">
        <img src="https://img.shields.io/github/license/mashape/apistatus.svg" alt="License">
    </a>
    <a href="https://github.com/laravel/laravel">
        <img src="https://img.shields.io/badge/awesome-laravel-ff69b4.svg" alt="License">
    </a>
</p>


## About FMock
A forums build with laravel.

FMock墨客社区。
> 客户端项目地址 https://github.com/ShyZhen/fmock-uniapp


## Environment
 > 必要
 - PHP >= 7.2.5
 - Mysql
 - Redis
 
 > 以下为非必要
 
 - Nodejs
 - ElasticSearch = 7.4.2
 - ElasticSearch-analysis-ik 7.4.2
 - RabbitMQ


## Installation

 #### 1.下载代码安装依赖
 - `git clone https://github.com/ShyZhen/fmock.git`
 - 创建项目数据库
 - `copy .env.example .env` and edit .env
 > 除了基本的APP配置、数据库配置、以及redis缓存配置（前四个代码块），仍需配置Smtp 邮箱服务、Sms短信服务、Github OAuth 第三方登录。
 > 根据自己vhost配置 `APP_URL` `CLIENT_URL` `SERVER_URL` `ADMIN_URL`
 > 如果想上传文件到七牛，需要开启`.env`中的`QiniuService=true`,并配置好七牛的各项参数。
 - composer 全量镜像不稳定，推荐更换`composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/`
 - `composer self-update` && `composer install`
 
 #### 2. 执行初始化安装
 - `php artisan fmock:install`
 
 #### 3. 权限设置
 - `chmod -R 766 storage/ && chmod -R 766 bootstrap/cache/` 根据自己实际用户组情况设置777或者766
 
 #### 4.异步、消息队列开启(目前仅有发送短信、邮件封装了redis队列，QueueStart=true时必须执行)
 - ~~使用redis做队列：`php artisan queue:work redis --queue=FMockTestQueue,sendSmsQueue --daemon --quiet --delay=3 --sleep=3 --tries=3`~~

 
## ES Quick Use
 #### Code Info
 - 新建es类并继承抽象类`Base/ElasticSearch`，例如PostElasticSearch
 - 必须实现抽象函数 `createIndex` 和 `getIndexName`，这样就可以完全使用基类中的任意方法（其中createIndex方法仅在es:init中使用）
 - 使用方法参考`Web/TestController@esTest`
 #### ES Init
 - `php artisan es:init`, 该命令将创建文章默认的index,并设置文章默认的mappings
 #### ES observer
 - 需要提前开启env中的ESToObserver
 - 创建Observers，例如`app/Observers/PostObserver.php`
 - 在`app/Providers/ObserversServiceProvider.php`中添加观察者模型,例如`Post::observe(PostObserver::class);`


## RabbitMQ Quick Use
 #### Code Info
 - 函数类库在`\app\Library\RabbitMQ`下,分别为生成类、消费类、消费回调业务函数
 #### Consume Bash Start
 - 启动消费脚本前要确定队列、交换机等存在，可以事先调用一次send：
 ```php
    $rabbitMQ = new Publish();
    $params = ['key1' => 'value1', 'key2' => 'value2', 'action' => 'sms'];
    print_r($rabbitMQ->send(env('RABBITMQ_QUEUE'), json_encode($params)));
```
 - 启动消费脚本命令：
 ```php
    php artisan rabbitmq:start
 ```
 #### Consume Callback
 - 启动消费脚本之后，所有的回调逻辑处理函数全部在`app\Library\RabbitMQ\RabbitMQHandle.php`文件中，你只需要更新此处即可

## API Info

 - 支持邮箱、手机号sms（阿里短信服务）验证码发送，以及完善的正则匹配
 - 支持邮箱、手机号（中国）登录注册
 - 多重验证，包括IP限制，账号尝试失败限制，有效避免爆破
 - 完全前后端分离模式，token鉴权，多端分开部署
 - 共用一套API接口代码，便于维护
 - 代码分层架构，controller service repo model 便于扩展
 - 支持GitHub第三方登录（后续会支持微信登录）
 - 支持微信小程序登录
 - 支持切换上传图片到七牛云与本地存储，使用七牛融合CDN进行静态资源加速
 - 七牛图片样式：fmock 最大宽度1080缩放，供内容详情使用；fmockmin 固定宽高剪裁，供首页海报图使用；
 - 记录用户上传文件日志，用户后续控制
 - 支持社区的基本操作：普通文章模块、问答模块、点赞、评论、搜藏
 - Delta格式富文本编辑器
 - 支持粉丝系统，查看用户关注、粉丝列表等操作，支持redis以及关系型数据库两种存储方式，量小推荐使用数据库
 - 支持ElasticSearch，使用Observer自动插入ES数据
 - 支持短视频上传七牛并切片、添加水印等操作（记得更新相关文件上传配置,例如nginx的client_body_temp/client_body_buffer_size 10/client_max_body_size 1024m）

## Other file

 - 代码结构
 ![代码结构](/doc/other/code.jpg)
 - [Postman合集(仅供参考)](/doc/other/Fmock.postman_collection.json)
 - [rp原型](/doc/other/fmock.rp)

## API Index
### [登录注册](/doc/auth.md)
- [发送注册验证码](/doc/auth.md#register-code)
- [用户注册](/doc/auth.md#register)
- [检查用户状态](/doc/auth.md#user-check)
- [登录](/doc/auth.md#login)
- [发送改密验证码](/doc/auth.md#password-code)
- [修改密码](/doc/auth.md#password)
- [我的信息](/doc/auth.md#me)
- [获取GitHub登录url](/doc/auth.md#github-login)
- [登出](/doc/auth.md#logout)

### [用户信息](/doc/user.md)
- [获取指定用户信息](/doc/user.md#user-info)
- [更新个人信息](/doc/user.md#post-me)
- [更新个人昵称](/doc/user.md#my-name)
- [某用户发布的所有评论(包括自己)](/doc/user.md#user-comments)
- [某用户发布的所有文章(包括自己)](/doc/user.md#user-posts)
- [某用户发布的所有(回答)文章(包括自己)](/doc/user.md#user-answers)
- [查看某个用户与自己的关注、互粉状态](/doc/user.md#follow-status-users)
- [查看某个用户的关注列表(包括自己)](/doc/user.md#follows-list)
- [查看某个用户的粉丝列表(包括自己)](/doc/user.md#fans-list)
- [查看我关注的用户们最新发布的文章、回答、视频](/doc/user.md#track-list)

### [文件上传相关](/doc/upload.md)
- [上传图片](/doc/upload.md#upload-image)
- [更换用户头像](/doc/upload.md#upload-avatar)
- [上传视频并入库](/doc/upload.md#upload-video)
- [前端上传获取token](/doc/upload.md#upload-token)
- [七牛工作流转码回调](/doc/upload.md#callback-qiniu)
- [保存数据入库](/doc/upload.md#post-video-item)
- [轮询videoItem获取转码结果](/doc/upload.md#get-video-item)

### [视频(集)模块](/doc/video.md)
- [更新视频信息,上传转码后调用](/doc/video.md#update-video-item)
- [删除我的某个视频](/doc/video.md#delete-video-item)

### [文章模块](/doc/post.md)
- [获取首页文章列表](/doc/post.md#posts)
- [获取指定文章](/doc/post.md#post)
- [新建文章](/doc/post.md#create-post)
- [更新指定文章](/doc/post.md#update-post)
- [删除指定文章](/doc/post.md#delete-post)
- [获取指定文章的回答](/doc/post.md#answers)
- [获取指定回答详情](/doc/post.md#answer)
- [新建(回答)文章](/doc/post.md#create-answer)
- [更新指定(回答)文章](/doc/post.md#update-answer)
- [删除指定(回答)文章](/doc/post.md#delete-answer)
- [获取我关注的所有文章](/doc/post.md#collections)
- [收藏某篇文章](/doc/post.md#post-collection)
- [取消收藏文章](/doc/post.md#delete-collection)

### [交互动作](/doc/action.md)
- [赞/取消赞（文章）](/doc/action.md#like-post)
- [踩/取消踩（文章）](/doc/action.md#dislike-post)
- [查看赞/踩/收藏状态（文章）](/doc/action.md#status-post)
- [赞/取消赞（回答）](/doc/action.md#like-answer)
- [踩/取消踩（回答）](/doc/action.md#dislike-answer)
- [查看赞/踩/收藏状态（回答）](/doc/action.md#status-answer)
- [赞/取消赞（评论）](/doc/action.md#like-comment)
- [踩/取消踩（评论）](/doc/action.md#dislike-comment)
- [查看赞/踩/收藏状态（评论）](/doc/action.md#status-comment)
- [赞/取消赞（视频）](/doc/action.md#like-video)
- [踩/取消踩（视频）](/doc/action.md#dislike-video)
- [查看赞/踩/收藏状态（视频）](/doc/action.md#status-video)
- [关注、取关某用户](/doc/action.md#follow-user)

### [评论相关](/doc/comment.md)
- [获取文章评论](/doc/comment.md#post-comment)
- [创建评论、回复](/doc/comment.md#create-post-comment)
- [删除自己的评论、回复](/doc/comment.md#delete-post-comment)
 
## [Admin管理后台](/doc/admin.md)
 
## Security Vulnerabilities

If you discover a security vulnerability within FMock, please send an e-mail to huaixiu.zhen via [huaixiu.zhen@gmail.com](mailto:huaixiu.zhen@gmail.com). All security vulnerabilities will be promptly addressed.

## License

The FMock is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

