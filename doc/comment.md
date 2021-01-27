## 评论模块

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
 ------------------------------

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
------------------------------

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
 ------------------------------
