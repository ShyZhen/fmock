## 上传相关

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
------------------------------

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
------------------------------

#### upload-video
 - POST `server_url/V1/file/video`
 - 上传视频，返回uuid (推荐使用客户端sdk上传)

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `video` | Y | File | Y | &lt;500M |  |

 - 返回值
 > HTTP/1.1 201 OK
 {"data" : <"uuid">}
 
 > HTTP/1.1 400、422
 {"message" : <"message">}
 ------------------------------
 
 #### upload-token
  - POST `server_url/V1/file/token/{type}`
  - 客户端使用七牛sdk上传，获取token
 
 参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
 |:---:|:---:|:---:|:---:|:---:|:---:|
 | `type` | Y | String | Y | `image/video` |  |
 
  - 返回值
  > HTTP/1.1 201 OK
  {"data" : <"token">}
  
  > HTTP/1.1 400、422
  {"message" : <"message">}
  ------------------------------
 
#### callback-qiniu
 - POST `server_url/V1/callback/qiniu`
 > https://developer.qiniu.com/dora/6504/receive-notifications
 ------------------------------

#### post-video-item
 - POST `server_url/V1/video/item`
 - 前端上传完成后，调用保存入库

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `key` | Y | String | Y |  | 文件名 |
| `hash` | Y | String | Y |  | hash |
| `url` | Y | String | Y |  | 带host的完整可访问路径 |

 - 返回值
 > HTTP/1.1 201 OK
 {"data" : <"video-item">}
 
 > HTTP/1.1 500
 {"message" : <"message">} 
 ------------------------------

#### get-video-item
 - GET `server_url/V1/video/item/{uuid}`
 - ajax轮询查询转码结果,通过is_transcode判断成功与否
 - is_transcode: 0成功、1等待处理、2处理中、3失败

参数 | 必须 | 类型 | 认证 | 长度 | 备注 |
|:---:|:---:|:---:|:---:|:---:|:---:|
| `uuid` | Y | String | Y |  |  |

 - 返回值
 > HTTP/1.1 200 OK
 {"data" : <"video-item">}
 
 > HTTP/1.1 404
 {"message" : <"message">} 
 ------------------------------
