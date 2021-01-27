## 用户信息相关

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
------------------------------

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
------------------------------

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
------------------------------

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
------------------------------

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
------------------------------

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
------------------------------

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
 ------------------------------

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
------------------------------

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
------------------------------

#### track-list
 - GET `server_url/V1/track/{type}`
 - 查看我关注的用户们最新发布的文章、回答、视频
 - 支持分页 `?page=x`

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `type` | Y | Enum | Y |  | 区分类型`post`/`answer`/`video` |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"list">}
 
 > HTTP/1.1 400
 {"message" : <"message">} 
------------------------------
