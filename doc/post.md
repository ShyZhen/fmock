## 文章模块


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
------------------------------

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
------------------------------

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
------------------------------

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
------------------------------

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
 ------------------------------

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
 ------------------------------

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
 ------------------------------

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
 ------------------------------

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
 ------------------------------

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
------------------------------

#### collections
 - GET `server_url/V1/collection/{type}`
 - 获取我收藏的文章、回答、视频
 - 支持分页操作

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `type` | Y | Enum | Y |  | 区分收藏的类型`post`/`answer`/`video` |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"collections">}
 
 > HTTP/1.1 404
 {"message" : <"message">}
------------------------------

#### post-collection
 - POST `server_url/V1/collection`
 - 收藏某篇文章、回答

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `resource_uuid` | Y | String | Y |  | 文章或回答的`uuid` |
| `type` | Y | Enum | Y |  | 区分收藏的类型`post`/`answer`/`video` |

 - 返回值
 > HTTP/1.1 200 OK
 {"message" : <"message">}
 
 > HTTP/1.1 404
 {"message" : <"message">}
------------------------------

#### delete-collection
 - DELETE `server_url/V1/collection/{type}/{uuid}`
 - 取消收藏文章、回答（不会返回失败，除非404，前端不需要toast提示）

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `type` | Y | Enum | Y |  | 区分类型`post`/`answer`/`video` |
| `uuid` | Y | String | Y |  | 该文章、回答的`uuid` |

 - 返回值
 > HTTP/1.1 200 OK
 {"message" : <"message">}
 
 > HTTP/1.1 404
 {"message" : <"message">}
------------------------------
