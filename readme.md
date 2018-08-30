<p align="center"><img src="https://www.litblc.com/usr/themes/pinghsu/images/favicon.ico"></p>

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


## API Index

- [registerCode](#register-code) | 发送注册验证码
- [register](#register) | 用户注册
- [login](#login) | 用户登录
- [passwordCode](#password-code) | 发送改密验证码
- [password](#password) | 修改密码
- [myInfo](#me) | 我的信息
- [logout](#logout) | 登出


- [getAllPosts](#posts) | 获取首页文章列表
- [getPostByUuid](#post) | 获取指定文章
- [createPost](#post-create) | 新建文章
- [updatePost](#post-update) | 更新指定文章
- [deletePost](#post-delete) | 删除指定文章


#### register-code
- POST `base_url/api/V1/register-code`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `email` | Y | String | N | &lt;255 | 用户表唯一 |

#### register
- POST `base_url/api/V1/register`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `name` | Y | String | N | &lt;16 |  |
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
| 无 |  |  |  |  |  |

#### logout
- GET `base_url/api/V1/logout`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  |  |  |  |

#### posts
- GET `base_url/api/V1/posts`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `sort` | N | String | N |  | 可选`sort/new` |
| `page` | N | Int | N |  | 分页 |

#### post
- GET `base_url/api/V1/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  |  |  |  |

#### post-create
- POST `base_url/api/V1/post`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `title` | Y | String | N | &lt;64 |  |
| `content` | Y | Int | N | &lt;10000 |  |

#### post-update
- PUT `base_url/api/V1/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `content` | Y | Int | N | &lt;10000 |  |

#### post-delete
- DELETE `base_url/api/V1/post/{uuid}`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| 无 |  |  |  |  |  |



## Security Vulnerabilities

If you discover a security vulnerability within Fmock, please send an e-mail to huaixiu.zhen via [huaixiu.zhen@gmail.com](mailto:huaixiu.zhen@gmail.com). All security vulnerabilities will be promptly addressed.

## License

The Fmock is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

