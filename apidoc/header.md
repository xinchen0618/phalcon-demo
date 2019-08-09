### 接口风格

RESTful 参考 https://www.vinaysahni.com/best-practices-for-a-pragmatic-restful-api

### Endpoint

项目由多个微服务组成

Service | 说明 | Endpoint 
---|---|---
account | 账号 | https://api.sfll.xyz/account/v1/

> 测试环境域名 https://api-testing.sfll.xyz

### Http Headers

header | 是否必填 | 说明
---|---|---
X-SFLL-Token | 否 | 授权token
X-SFLL-From | 是 | 请求来源, 当前为固定值sfll-miniprogram
  
### 错误码

服务器错误统一返回500, 客户端错误返回4xx

- 示例
```
HTTP/1.1 404 Not Found
{
    "status": "ResourceNotFound",
    "message": "您请求资源不存在"
}
```

- 说明
 
Http Status Code | status | message
---|---|---
500 | Exception | 服务异常，请稍后重试
404 | ResourceNotFound | 您请求资源不存在

> 测试环境 Exception message 为异常详细内容

