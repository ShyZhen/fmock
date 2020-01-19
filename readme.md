<p align="center"><img src="https://www.litblc.com/usr/themes/pinghsu/images/favicon.ico"></p>
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

~~我也不知道要做成一个什么东西。~~

FMock墨客社区。


## Environment
 - PHP >= 7.1.3
 - Mysql
 - Redis
 - Nodejs
 - ElasticSearch = 7.4.2
 - ElasticSearch-analysis-ik 7.4.2


## Installation

 #### 下载代码安装依赖
 - `git clone https://github.com/ShyZhen/fmock.git`
 - `copy .env.example .env` and edit .env (生产环境记得修改env中APP_ENV=production)
 > 除了基本的APP配置、数据库配置、以及redis缓存配置（前四个代码块），仍需配置Smtp 邮箱服务、Sms短信服务、Github OAuth 第三方登录。
 如果想上传文件到七牛，需要开启`.env`中的`QiniuService=true`,并配置好七牛的各项参数。
 - composer 全量镜像不稳定，推荐更换`composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/`
 - `composer self-update` && `composer install`
 
 #### 执行初始化安装
 - `php artisan fmock:install`
 
 #### 权限设置
 - `chmod -R 766 storage/` and `chmod -R 766 bootstrap/cache/`
 - ~~`php artisan queue:work redis --queue=FMock --daemon --quiet --delay=3 --sleep=3 --tries=3`~~

 
## ES Quick Use
 - 新建es类并继承抽象类`Base/ElasticSearch`，例如PostElasticSearch
 - 必须实现抽象函数 `createIndex` 和 `getIndexName`，这样就可以完全使用基类中的任意方法（其中createIndex方法仅在es:init中使用）
 - 使用方法参考`Web/TestController@esTest`
#### ES Init
 - `php artisan es:init`, 该命令将创建文章默认的index,并设置文章默认的mappings
#### ES observer
 - 需要提前开启env中的ESToObserver
 - 创建Observers，例如`app/Observers/PostObserver.php`
 - 在`app/Providers/ObserversServiceProvider.php`中添加观察者模型,例如`Post::observe(PostObserver::class);`


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
 - 记录用户上传文件日志，用户后续控制
 - 支持社区的基本操作：普通文章模块、问答模块、点赞、评论、搜藏
 - Delta格式富文本编辑器
 - 支持粉丝系统，查看用户关注、粉丝列表等操作，支持redis以及关系型数据库两种存储方式，量小推荐使用数据库
 - 支持ElasticSearch，使用Observer自动插入ES数据
 - 支持短视频上传七牛并切片、添加水印等操作（记得更新最大上传文件限制、最大执行时间）

## API Index

 - [registerCode](#register-code) | 发送注册验证码
 - [register](#register) | 用户注册
 - [getAccountStatus](#user-check) | 检查用户状态
 - [login](#login) | 用户登录
 - [passwordCode](#password-code) | 发送改密验证码
 - [password](#password) | 修改密码
 - [myInfo](#me) | 我的信息
 - [githubLogin](#github-login) | 获取GitHub登录url
 
 - [userInfo](#user-info) | 获取指定用户信息
 - [updateMyInfo](#post-me) | 更新个人信息
 - [updateMyName](#my-name) | 更新个人昵称
 - [uploadImage](#upload-image) | 上传图片
 - [uploadAvatar](#upload-avatar) | 更换用户头像
 - [logout](#logout) | 登出
 
  - [uploadVideo](#upload-video) | 上传视频并入库
 
 - [getAllPosts](#posts) | 获取首页文章列表
 - [getPostByUuid](#post) | 获取指定文章
 - [createPost](#create-post) | 新建文章
 - [updatePost](#update-post) | 更新指定文章
 - [deletePost](#delete-post) | 删除指定文章
 
 
 - [getAnswerByPostUuid](#answers) | 获取指定文章的回答
 - [getAnswerByUuid](#answer) | 获取指定回答详情
 - [createAnswer](#create-answer) | 新建(回答)文章
 - [updateAnswer](#update-answer) | 更新指定(回答)文章
 - [deleteAnswer](#delete-answer) | 删除指定(回答)文章
 
 
 - [getMyFollowedPosts](#collections) | 获取我关注的所有文章
 - [followedPost](#post-collection) | 关注指定文章
 - [unFollow](#delete-collection) | 取消关注文章
 
 - [likePost](#like-post) | 赞/取消赞（文章）
 - [dislikePost](#dislike-post) | 踩/取消踩（文章）
 - [statusPost](#status-post) | 查看赞/踩状态（文章）
 
 - [likeAnswer](#like-answer) | 赞/取消赞（回答）
 - [dislikeAnswer](#dislike-answer) | 踩/取消踩（回答）
 - [statusAnswer](#status-answer) | 查看赞/踩状态（回答）
 
 - [likeComment](#like-comment) | 赞/取消赞（评论）
 - [dislikeComment](#dislike-comment) | 踩/取消踩（评论）
 - [statusComment](#status-comment) | 查看赞/踩状态（评论）
 
 - [getCommentByPostUuid](#post-comment) | 获取文章评论
 - [createComment](#create-post-comment) | 创建评论、回复
 - [deleteComment](#delete-post-comment) | 删除自己的评论、回复
 
 - [userComment](#user-comments) | 某用户发布的所有评论(包括自己)
 - [userPost](#user-posts) | 某用户发布的所有文章(包括自己)
 - [userAnswer](#user-answers) | 某用户发布的所有(回答)文章(包括自己)

 - [follow](#follow-user) | 关注、取关某用户
 - [status](#follow-status-user) | 查看某个用户与自己的关注、互粉状态
 - [getFansList](#follows-list) | 查看某个用户的关注列表(包括自己)
 - [getFansList](#fans-list) | 查看某个用户的粉丝列表(包括自己)


#### register-code
 - POST `server_url/V1/register-code`
 - 发送注册验证码

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `account` | Y | String | N | &lt;255 | 邮箱或者手机，用户表唯一 |

 - 返回值
 > HTTP/1.1 200 OK
 {"message" : <"message">}
 
 > HTTP/1.1 400、403、422、500
 {"message" : <"message">}

#### register
 - POST `server_url/V1/register`
 - 注册动作

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `name` | Y | String | N | &lt;16 | 用户表唯一 |
| `account` | Y | String | N | &lt;255 | 用户表唯一 |
| `verify_code` | Y | Int | N | 6 |  |
| `password` | Y | String | N | &lt;255 |  |
| `password_confirmation` | Y | String | N | &lt;255 |  |

 - 返回值
 > HTTP/1.1 201 OK
 {"access_token" : <"token">}
 
 > HTTP/1.1 400、401、422
 {"message" : <"message">}

#### user-check
 - POST `server_url/V1/user-check`
 - 检查用户是否合法（是否存在、是否冻结）

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `account` | Y | String | N | &lt;255 | 必须存在于用户表 |

 - 返回值
 > HTTP/1.1 204 OK
 {null}
 
 > HTTP/1.1 400、403
 {"message" : <"message">}

#### login
 - POST `server_url/V1/login`
 - 登录

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `account` | Y | String | N | &lt;255 |  |
| `password` | Y | String | N | &gt;6 |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"access_token" : <"token">}
 
 > HTTP/1.1 400、403、422
 {"message" : <"message">}

#### password-code
 - POST `server_url/V1/password-code`
 - 忘记密码时，发送验证码

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `account` | Y | String | N | &lt;255 | 必须存在于用户表 |

 - 返回值
 > HTTP/1.1 200 OK
 {"message" : <"message">}
 
 > HTTP/1.1 400、403、422、500
 {"message" : <"message">}

#### password
 - POST `server_url/V1/password`
 - 改密

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `account` | Y | String | N | &lt;255 | 必须存在于用户表 |
| `verify_code` | Y | Int | N | 6 |  |
| `password` | Y | String | N | &lt;255 |  |
| `password_confirmation` | Y | String | N | &lt;255 |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"message" : <"message">}
 
 > HTTP/1.1 400、401、403、422
 {"message" : <"message">}

#### me
 - GET `server_url/V1/me`
 - 获取我（当前登录者）的信息

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y | |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"data">}
 
 > HTTP/1.1 401
 {"message" : <"message">}

#### github-login
 - GET `server_url/V1/oauth/github/login`
 - 第三方Github登录，返回第三方登录的重定向链接

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | N | |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"redirectUrl" : <"redirectUrl">}
 
 > HTTP/1.1 500
 {"message" : <"message">}
 
#### user-info
 - GET `server_url/V1/user/{uuid}`
 - 查看用户信息

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `uuid` | Y | String | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"userInfo">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### post-me
 - POST `server_url/V1/me`
 - 修改我的个人信息

 > 可不填，传递空字符串‘’，不可传递null
 
参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `gender` | N | Enum | Y | &lt;255 | male/female/secrecy |
| `birthday` | N | Date | Y |  | 形如2018-06-08 |
| `reside_city` | N | String | Y | &lt;16 | 居住地 |
| `bio` | N | String | Y | &lt;32 | 一句话介绍 |
| `intro` | N | String | Y | &lt;128 | 个人介绍 |
| `company` | N | String | Y | &lt;32 | 公司 |
| `company_type` | N | String | Y | &lt;32 | 行业 |
| `position` | N | String | Y | &lt;32 | 职位 |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"userInfo">}
 
 > HTTP/1.1 400、500
 {"message" : <"message">}

#### my-name
 - POST `server_url/V1/my-name`
 - 修改昵称（不可重复）

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `name` | Y | String | Y | &lt;20 | 用户表唯一 |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"userName">}
 
 > HTTP/1.1 400、403、500
 {"message" : <"message">}

#### upload-image
 - POST `server_url/V1/file/image`
 - 上传图片，返回全路径链接

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `image` | Y | File | Y | &lt;5000KB | jpg,jpeg,png,gif |

 - 返回值
 > HTTP/1.1 201 OK
 {"data" : <"imageUrl">}
 
 > HTTP/1.1 400、422
 {"message" : <"message">}

#### upload-avatar
 - POST `server_url/V1/file/avatar`
 - 上传头像，返回全路径链接

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `avatar` | Y | File | Y | &lt;1000KB | jpg,jpeg,png,gif |

 - 返回值
 > HTTP/1.1 201 OK
 {"data" : <"imageUrl">}
 
 > HTTP/1.1 400、422
 {"message" : <"message">}

#### logout
 - GET `server_url/V1/logout`
 - 登出，token失效

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"message" : <"message">}
 
#### upload-video
 - POST `server_url/V1/file/video`
 - 上传视频，返回uuid

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `video` | Y | File | Y | &lt;500M |  |

 - 返回值
 > HTTP/1.1 201 OK
 {"data" : <"uuid">}
 
 > HTTP/1.1 400、422
 {"message" : <"message">}

 
#### posts
 - GET `server_url/V1/posts`
 - 获取首页文章列表
 - 支持分页

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `type` | N | String | N |  | 可选`hot/all/share/question/dynamite/friend/recruit` |
| `page` | N | Int | N |  | 分页 |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"posts">}
 
 > HTTP/1.1 400
 {"message" : <"message">}

#### post
 - GET `server_url/V1/post/{uuid}`
 - 获取某篇文章详细信息

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `uuid` | Y |  | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"post">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### create-post
 - POST `server_url/V1/post`
 - 创建文章

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `title` | Y | String | Y | &lt;64 |  |
| `summary` | Y | String | Y | &lt;80 |  |
| `poster` | Y | String | Y | &lt;128 |  |
| `content` | Y | Int | Y | &lt;10000 |  |
| `anonymous` | Y | Boolean | Y |  | 是否匿名发布 |
| `type` | Y | String | Y |  | 可选`share/question/dynamite/friend/recruit` |

 - 返回值
 > HTTP/1.1 201 OK
 {"data" : <"uuid">}
 
 > HTTP/1.1 400、422、500
 {"message" : <"message">}

#### update-post
 - PUT `server_url/V1/post/{uuid}`
 - 修改某篇文章（不得修改标题）

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `summary` | Y | String | Y | &lt;80 |  |
| `poster` | Y | String | Y | &lt;128 |  |
| `content` | Y | Int | Y | &lt;10000 |  |
| `anonymous` | Y | Boolean | Y |  | 是否匿名发布 |
| `type` | Y | String | Y |  | 可选`share/question/dynamite/friend/recruit` |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"post">}
 
 > HTTP/1.1 400、404、500
 {"message" : <"message">}

#### delete-post
 - DELETE `server_url/V1/post/{uuid}`
 - 删除我的某篇文章

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `uuid` | Y |  | Y |  |  |

 - 返回值
 > HTTP/1.1 204 OK
 {null}
 
 > HTTP/1.1 404、500
 {"message" : <"message">}
 
#### answers
 - GET `server_url/V1/answers/{postUuid}/{type?}`
 - 获取某个文章（问题）的回答列表
 - 支持分页
 
参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `postUuid` | Y | String | Y |  |  |
| `type` | N | String | Y |  | 可选`hot/new`默认`new` |
| `page` | N | Int | Y |  | 分页 |
 
 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"answers">}
 
 > HTTP/1.1 404
 {"message" : <"message">}
 
#### answer
 - GET `server_url/V1/answer/detail/{uuid}`
 - 获取某个回答的详细信息
 
参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `uuid` | Y | String | Y |  |  |
 
 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"answer">}
 
 > HTTP/1.1 404
 {"message" : <"message">}
 
#### create-answer
 - POST `server_url/V1/answer`
 - 写回答
 
参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `post_uuid` | Y | String | Y | &lt;64 |  |
| `title` | Y | String | Y | &lt;64 |  |
| `summary` | Y | String | Y | &lt;80 |  |
| `poster` | Y | String | Y | &lt;128 |  |
| `content` | Y | Int | Y | &lt;10000 |  |
| `anonymous` | Y | Boolean | Y |  | 是否匿名发布 |
 
 - 返回值
 > HTTP/1.1 201 OK
 {"data" : <"uuid">}
 
 > HTTP/1.1 400、404、422、500
 {"message" : <"message">}
 
#### update-answer
 - PUT `server_url/V1/answer/{uuid}`
 - 修改我的某篇回答
 
参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `summary` | Y | String | Y | &lt;80 |  |
| `poster` | Y | String | Y | &lt;128 |  |
| `content` | Y | Int | Y | &lt;10000 |  |
| `anonymous` | Y | Boolean | Y |  | 是否匿名发布 |
 
 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"answer">}
 
 > HTTP/1.1 400、404、500
 {"message" : <"message">}
 
#### delete-answer
 - DELETE `server_url/V1/answer/{uuid}`
 - 删除我的某篇回答
 
参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `uuid` |  |  | Y |  |  |

 - 返回值
 > HTTP/1.1 204 OK
 {null}
 
 > HTTP/1.1 404、500
 {"message" : <"message">}

#### collections
 - GET `server_url/V1/collection/{type}`
 - 获取我收藏的文章、回答
 - 支持分页操作

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `type` | Y | Enum | Y |  | 区分收藏的类型`post`/`answer` |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"collections">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### post-collection
 - POST `server_url/V1/collection`
 - 收藏某篇文章、回答

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `resource_uuid` | Y | String | Y |  | 文章或回答的`uuid` |
| `type` | Y | Enum | Y |  | 区分收藏的类型`post`/`answer` |

 - 返回值
 > HTTP/1.1 200 OK
 {"message" : <"message">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### delete-collection
 - DELETE `server_url/V1/collection/{type}/{uuid}`
 - 取消收藏文章、回答（不会返回失败，除非404，前端不需要toast提示）

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `type` | Y | Enum | Y |  | 区分类型`post`/`answer` |
| `uuid` | Y | String | Y |  | 该文章、回答的`uuid` |

 - 返回值
 > HTTP/1.1 200 OK
 {"message" : <"message">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### like-post
 - POST `server_url/V1/like/post/{uuid}`
 - 赞文章,再次请求取消赞

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `uuid` | Y |  | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"message" : <"message">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### dislike-post
 - POST `server_url/V1/dislike/post/{uuid}`
 - 踩文章,再次请求取消踩

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `uuid` | Y |  | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"message" : <"message">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### status-post
 - GET `server_url/V1/status/post/{uuid}`
 - 查询状态

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"data">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### like-answer
 - POST `server_url/V1/like/answer/{uuid}`
 - 赞回答,再次请求取消赞

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `uuid` | Y |  | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"message" : <"message">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### dislike-answer
 - POST `server_url/V1/dislike/answer/{uuid}`
 - 踩回答,再次请求取消踩

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `uuid` | Y |  | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"message" : <"message">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### status-answer
 - GET `server_url/V1/status/answer/{uuid}`
 - 查询状态

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"data">}
 
 > HTTP/1.1 404
 {"message" : <"message">}
 
#### like-comment
 - POST `server_url/V1/like/comment/{id}`
 - 赞评论,再次请求取消赞（这里url跟着评论的ID,而不是uuid）

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `id` | Y |  | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"message" : <"message">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### dislike-comment
 - POST `server_url/V1/dislike/comment/{id}`
 - 踩评论,再次请求取消踩（这里url跟着评论的ID,而不是uuid）

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `id` | Y |  | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"message" : <"message">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### status-comment
 - GET `server_url/V1/status/comment/{id}`
 - 查询状态

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"data">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### post-comment
 - GET `server_url/V1/comment/{type}/{postUuid}/{sort?}`
 - 获取文章或回答的评论列表

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `type` | Y | Enum | Y |  | 只能在`answer`/`post`中选取 |
| `postUuid` | Y | String | Y |  | 资源的uuid |
| `sort` | N | String | Y |  | `{sort}`可选new/hot,默认new |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"comments">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### create-post-comment
 - POST `server_url/V1/comment`
 - 写评论、回复评论

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `resource_uuid` | Y | String | Y |  | 资源ID |
| `parent_id` | Y | Int | Y |  | 是否有父评论（即是否是回复），没有填0 |
| `content` | Y | String | Y | &lt;500 |  |
| `type` | Y | Enum | Y |  | 只能在`answer`/`post`中选取 |

 - 返回值
 > HTTP/1.1 201 OK
 {"data" : <"comment">}
 
 > HTTP/1.1 400、404、422、500
 {"message" : <"message">}

#### delete-post-comment
 - DELETE `server_url/V1/comment/{id}`
 - 删除我的某条评论

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `id` | Y | Int | Y |  | 删除评论传递的是评论ID，评论表没有uuid |

 - 返回值
 > HTTP/1.1 204 Not Content
 {null}
 
 > HTTP/1.1 404、500
 {"message" : <"message">}

#### user-comments
 - GET `server_url/V1/user/comments/{userUuid}`
 - 获取某个用户曾经的所有评论
 - 支持分页

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `userUuid` | Y | String | Y |  | 可以通过type和resource_uuid找到原始文章 |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"comments">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### user-posts
 - GET `server_url/V1/user/posts/{userUuid}`
 - 获取某个用户的所有文章
 - 支持分页

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `userUuid` | Y | String | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"comments">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### user-answers
 - GET `server_url/V1/user/answers/{userUuid}`
 - 获取某个用户的所有回答
 支持分页

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `userUuid` | Y | String | Y |  |  |

  - 返回值
  > HTTP/1.1 200 OK
  {"data" : <"answers">}
  
  > HTTP/1.1 404
  {"message" : <"message">}




#### follow-user
 - POST `server_url/V1/follow/{userUuid}`
 - 关注、取关某人

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `userUuid` | Y | String | Y |  |  |

  - 返回值
  > HTTP/1.1 200 OK
  {"message" : <"message">}
  
  > HTTP/1.1 404、422
  {"message" : <"message">}
  
#### follow-status-user
 - GET `server_url/V1/follow/status/{userUuid}`
 - 查询对某个用户的关注、互粉状态

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `userUuid` | Y | String | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"data">}
 
 > HTTP/1.1 404、422
 {"message" : <"message">} 
 
#### follows-list
 - GET `server_url/V1/follows/list/{userUuid}`
 - 查询某个用户的关注列表（包括我自己）
 - 支持分页 `?page=x`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `userUuid` | Y | String | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"list">}
 
 > HTTP/1.1 404
 {"message" : <"message">} 

#### fans-list
 - GET `server_url/V1/fans/list/{userUuid}`
 - 查询某个用户的粉丝列表（包括我自己）
 - 支持分页 `?page=x`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `userUuid` | Y | String | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"list">}
 
 > HTTP/1.1 404
 {"message" : <"message">} 

## Security Vulnerabilities

If you discover a security vulnerability within FMock, please send an e-mail to huaixiu.zhen via [huaixiu.zhen@gmail.com](mailto:huaixiu.zhen@gmail.com). All security vulnerabilities will be promptly addressed.

## License

The FMock is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

