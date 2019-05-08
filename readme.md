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
 - PHP >= 7.0.0
 - Mysql
 - Redis
 - Nodejs
 - ElasticSearch = 6.2.4 && ElasticSearch-analysis-ik (one index,one type)


## Installation
 - `git clone https://github.com/ShyZhen/fmock.git`
 - `copy .env.example .env` and edit .env
 > 除了基本的APP配置、数据库配置、以及redis缓存配置（前四个代码块），仍需配置Smtp 邮箱服务、Sms短信服务、Github OAuth 第三方登录。
 如果想上传文件到七牛，需要开启`.env`中的`QiniuService=true`,并配置好七牛的各项参数。
 - `composer install`
 - `php artisan key:generate`
 - `php artisan storage:link`
 - `chmod -R 766 storage/` and `chmod -R 766 bootstrap/cache/`
 - `php artisan migrate`
 - `php artisan passport:install`
 - ~~`php artisan queue:work redis --queue=FMock --daemon --quiet --delay=3 --sleep=3 --tries=3`~~


## API Info

 - 支持邮箱、手机号sms（阿里短信服务）验证码发送，以及完善的正则匹配
 - 支持邮箱、手机号（中国）登录注册
 - 多重验证，包括IP限制，账号尝试失败限制，有效避免爆破
 - 完全前后端分离模式，token鉴权，多端分开部署
 - 共用一套API接口代码，便于维护
 - 代码分层架构，controller service repo model 便于扩展
 - 支持GitHub第三方登录（后续会支持微信登录）
 - 支持切换上传图片到七牛云与本地存储，使用七牛融合CDN进行静态资源加速
 - 记录用户上传文件日志，用户后续控制

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

- [getAllPosts](#posts) | 获取首页文章列表
- [getPostByUuid](#post) | 获取指定文章
- [createPost](#create-post) | 新建文章
- [updatePost](#update-post) | 更新指定文章
- [deletePost](#delete-post) | 删除指定文章

- [getMyFollowedPosts](#follows) | 获取我关注的所有文章
- [followedPost](#post-follow) | 关注指定文章
- [unFollow](#delete-follow) | 取消关注文章
- [likePost](#like-post) | 赞/取消赞（文章）
- [dislikePost](#dislike-post) | 踩/取消踩（文章）
- [statusPost](#status-post) | 查看赞/踩状态（文章）

- [getCommentByPostUuid](#post-comment) | 获取文章评论
- [createComment](#create-post-comment) | 创建评论、回复
- [deleteComment](#delete-post-comment) | 删除自己的评论、回复

- [likeComment](#like-comment) | 赞/取消赞（评论）
- [dislikeComment](#dislike-comment) | 踩/取消踩（评论）
- [statusComment](#status-comment) | 查看赞/踩状态（评论）

- [userComment](#user-comments) | 某用户发布的所有评论(包括自己)
- [userPost](#user-posts) | 某用户发布的所有文章(包括自己)



#### register-code
- POST `server_url/V1/register-code`

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

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  |  | Y |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"data">}
 
 > HTTP/1.1 401
 {"message" : <"message">}

#### github-login
- GET `server_url/V1/oauth/github/login`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  |  | N |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"redirectUrl" : <"redirectUrl">}
 
 > HTTP/1.1 500
 {"message" : <"message">}
 
#### user-info
- GET `server_url/V1/user/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"userInfo">}
 
 > HTTP/1.1 404
 {"message" : <"message">}

#### post-me
- POST `server_url/V1/me`

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

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `image` | Y | File | Y | &lt;5000KB | jpg,jpeg,png,gif |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"imageUrl">}
 
 > HTTP/1.1 400、422
 {"message" : <"message">}

#### upload-avatar
- POST `server_url/V1/file/avatar`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `avatar` | Y | File | Y | &lt;1000KB | jpg,jpeg,png,gif |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"imageUrl">}
 
 > HTTP/1.1 400、422
 {"message" : <"message">}

#### logout
- GET `server_url/V1/logout`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"message" : <"message">}
 
#### posts
- GET `server_url/V1/posts`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `sort` | N | String | N |  | 可选`post-new/post-hot/post-anonymous` |
| `page` | N | Int | N |  | 分页 |

#### post
- GET `server_url/V1/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### create-post
- POST `server_url/V1/post`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `title` | Y | String | Y | &lt;64 |  |
| `content` | Y | Int | Y | &lt;10000 |  |
| `anonymous` | Y | Boolean | Y |  | 是否匿名发布 |

#### update-post
- PUT `server_url/V1/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `content` | Y | Int | Y | &lt;10000 |  |

#### delete-post
- DELETE `server_url/V1/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### follows
- GET `server_url/V1/follow/posts`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### post-follow
- POST `server_url/V1/follow/post`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `uuid` | Y | String | Y |  | 文章`uuid` |.

#### delete-follow
- DELETE `server_url/V1/follow/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### like-post
- GET `server_url/V1/like/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### dislike-post
- GET `server_url/V1/dislike/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### status-post
- GET `server_url/V1/status/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### post-comment
- GET `server_url/V1/comment/{postUuid}/{type?}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  | `{type}`可选new/hot,默认new |

#### create-post-comment
- POST `server_url/V1/comment`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `post_uuid` | Y | String | Y |  |  |
| `parent_id` | Y | Int | Y |  |  |
| `content` | Y | String | Y | &lt;500 |  |

#### delete-post-comment
- DELETE `server_url/V1/comment/{id}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  | 删除评论传递的是评论ID，评论表没有uuid |

#### like-comment
- GET `server_url/V1/like/comment/{id}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### dislike-comment
- GET `server_url/V1/dislike/comment/{id}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### status-comment
- GET `server_url/V1/status/comment/{id}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |


#### user-comments
- GET `server_url/V1/user/comments/{userUuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### user-posts
- GET `server_url/V1/user/posts/{userUuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |


## Security Vulnerabilities

If you discover a security vulnerability within FMock, please send an e-mail to huaixiu.zhen via [huaixiu.zhen@gmail.com](mailto:huaixiu.zhen@gmail.com). All security vulnerabilities will be promptly addressed.

## License

The FMock is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

