### 接口风格

RESTful 参考 https://www.vinaysahni.com/best-practices-for-a-pragmatic-restful-api

示例:
```
curl -X POST \
  https://api.sfll.net/comment/v1/comments \
  -H 'Content-Type: application/json' \
  -H 'X-SFLL-Token: vuomdg39uevkajtnismb68r407' \
  -d '{
	"order_id": 62,
	"content": "服务很满意"
}'
```

### Endpoint

项目由多个Module组成

Module | 说明 | Endpoint 
---|---|---
account | 账号 | https://api.sfll.xyz/account/v1/

> 测试环境域名 https://api-testing.sfll.xyz

### Http Headers

header | 是否必填 | 说明
---|---|---
X-SFLL-Token | 否 | 前端授权token
X-SFLL-Admin-Token | 否 | 管理端授权token
  
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

