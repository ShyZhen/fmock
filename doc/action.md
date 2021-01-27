## 交互动作

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
------------------------------

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
------------------------------

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
------------------------------

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
------------------------------

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
------------------------------

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
 ------------------------------
 
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
 ------------------------------
 
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
 ------------------------------
 
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
 ------------------------------
 
 #### like-video
  - POST `server_url/V1/like/video/{uuid}`
  - 赞视频,再次请求取消赞
 
 参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
 |:---:|:---:|:---:|:---:|:---:|:---:|
 | `uuid` | Y |  | Y |  |  |
 
  - 返回值
  > HTTP/1.1 200 OK
  {"message" : <"message">}
  
  > HTTP/1.1 404
  {"message" : <"message">}
 ------------------------------
 
 #### dislike-video
  - POST `server_url/V1/dislike/video/{uuid}`
  - 踩视频,再次请求取消踩
 
 参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
 |:---:|:---:|:---:|:---:|:---:|:---:|
 | `uuid` | Y |  | Y |  |  |
 
  - 返回值
  > HTTP/1.1 200 OK
  {"message" : <"message">}
  
  > HTTP/1.1 404
  {"message" : <"message">}
 ------------------------------
 
 #### status-video
  - GET `server_url/V1/status/video/{uuid}`
  - 查询状态
 
 参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
 |:---:|:---:|:---:|:---:|:---:|:---:|
 | 无 |  |  | Y |  |  |
 
  - 返回值
  > HTTP/1.1 200 OK
  {"data" : <"data">}
  
  > HTTP/1.1 404
  {"message" : <"message">}
 ------------------------------

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
 ------------------------------
