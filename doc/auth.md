## 登录注册相关

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
------------------------------

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
------------------------------

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
------------------------------

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
------------------------------

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
------------------------------

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
------------------------------

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
------------------------------

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
 ------------------------------
 
 #### logout
  - GET `server_url/V1/logout`
  - 登出，token失效
 
 参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
 |:---:|:---:|:---:|:---:|:---:|:---:|
 | 无 |  |  | Y |  |  |
 
  - 返回值
  > HTTP/1.1 200 OK
  {"message" : <"message">}
  ------------------------------
