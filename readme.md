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


## About Fmock
A forums build with laravel.

我也不知道要做成一个什么东西。


## Environment
 - PHP >= 7.0.0
 - Mysql
 - Redis
 - Nodejs
 - ElasticSearch = 6.2.4 && ElasticSearch-analysis-ik (one index,one type)


## Installation
 - `git clone https://github.com/ShyZhen/fmock.git`
 - `copy .env.example .env` and edit .env
 - `composer install`
 - `php artisan key:generate`
 - `php artisan storage:link`
 - `php artisan migrate`
 - `php artisan passport:install`
 - `php artisan queue:work redis --queue=FMock --daemon --quiet --delay=3 --sleep=3 --tries=3`


## API Index

- [registerCode](#register-code) | 发送注册验证码
- [register](#register) | 用户注册
- [login](#login) | 用户登录
- [passwordCode](#password-code) | 发送改密验证码
- [password](#password) | 修改密码
- [myInfo](#me) | 我的信息
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
- [likePost](#like-post) | 赞/取消赞
- [dislikePost](#dislike-post) | 踩/取消踩
- [statusPost](#status-post) | 查看赞/踩状态

- [getCommentByPostUuid](#post-comment) | 获取文章评论
- [createComment](#create-post-comment) | 创建评论、回复
- [deleteComment](#delete-post-comment) | 删除自己的评论、回复



#### register-code
- POST `base_url/api/V1/register-code`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `email` | Y | String | N | &lt;255 | 用户表唯一 |

#### register
- POST `base_url/api/V1/register`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `name` | Y | String | N | &lt;16 | 用户表唯一 |
| `email` | Y | String | N | &lt;255 | 用户表唯一 |
| `verify_code` | Y | Int | N | 6 |  |
| `password` | Y | String | N | &lt;255 |  |
| `password_confirmation` | Y | String | N | &lt;255 |  |

#### login
- POST `base_url/api/V1/login`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `email` | Y | String | N | &lt;255 |  |
| `password` | Y | String | N | &gt;6 |  |

#### password-code
- POST `base_url/api/V1/password-code`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `email` | Y | String | N | &lt;255 | 存在于用户表 |

#### password
- POST `base_url/api/V1/password`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `email` | Y | String | N | &lt;255 | 存在于用户表 |
| `verify_code` | Y | Int | N | 6 |  |
| `password` | Y | String | N | &lt;255 |  |
| `password_confirmation` | Y | String | N | &lt;255 |  |

#### me
- GET `base_url/api/V1/me`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  |  | Y |  |

#### user-info
- GET `base_url/api/V1/user/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### post-me
- POST `base_url/api/V1/me`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `gender` | N | Enum | Y | &lt;255 | male/female/secrecy |
| `birthday` | N | Date | Y |  | 形如2018-06-08 |
| `reside_city` | N | String | Y | &lt;16 |  |
| `bio` | N | String | Y | &lt;32 |  |

#### my-name
- POST `base_url/api/V1/my-name`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `name` | Y | String | Y | &lt;20 | 用户表唯一 |

#### upload-image
- POST `base_url/api/V1/file/image`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `image` | Y | File | Y | &lt;5000KB | jpg,jpeg,png,gif |

#### upload-avatar
- POST `base_url/api/V1/file/avatar`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `avatar` | Y | File | Y | &lt;1000KB | jpg,jpeg,png,gif |

#### logout
- GET `base_url/api/V1/logout`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### posts
- GET `base_url/api/V1/posts`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `sort` | N | String | N |  | 可选`post-new/post-hot/post-anonymous` |
| `page` | N | Int | N |  | 分页 |

#### post
- GET `base_url/api/V1/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### create-post
- POST `base_url/api/V1/post`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `title` | Y | String | Y | &lt;64 |  |
| `content` | Y | Int | Y | &lt;10000 |  |
| `anonymous` | Y | Boolean | Y |  | 是否匿名发布 |

#### update-post
- PUT `base_url/api/V1/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `content` | Y | Int | Y | &lt;10000 |  |

#### delete-post
- DELETE `base_url/api/V1/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### follows
- GET `base_url/api/V1/follow/posts`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### post-follow
- POST `base_url/api/V1/follow/post`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `uuid` | Y | String | Y |  | 文章`uuid` |.

#### delete-follow
- DELETE `base_url/api/V1/follow/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### like-post
- GET `base_url/api/V1/like/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### dislike-post
- GET `base_url/api/V1/dislike/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### status-post
- GET `base_url/api/V1/status/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  |  |

#### post-comment
- GET `base_url/api/V1/comment/{postUuid}/{type?}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  | `{type}`可选new/hot,默认new |

#### create-post-comment
- POST `base_url/api/V1/comment`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `post_uuid` | Y | String | Y |  |  |
| `parent_id` | Y | Int | Y |  |  |
| `content` | Y | String | Y | &lt;500 |  |

#### delete-post-comment
- DELETE `base_url/api/V1/comment/{id}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  | Y |  | 删除评论传递的是评论ID，评论表没有uuid |


## Security Vulnerabilities

If you discover a security vulnerability within Fmock, please send an e-mail to huaixiu.zhen via [huaixiu.zhen@gmail.com](mailto:huaixiu.zhen@gmail.com). All security vulnerabilities will be promptly addressed.

## License

The Fmock is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

