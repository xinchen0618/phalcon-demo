### 接口风格

RESTful, 参考 <a href="https://www.vinaysahni.com/best-practices-for-a-pragmatic-restful-api" target="_blank">Best Practices for Designing a Pragmatic RESTful API</a>

接口请求示例:
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

- 域名

环境 | 域名
---|---
生产 | https://api.sfll.xyz
测试 | https://api-testing.sfll.xyz

- Module

项目由多Module组成, Module均单独维护版本

Module | 说明 | Path
---|---|---
account | 前端账号相关 | /account/v1


### 自定专用HTTP Headers

Header | 是否必填 | 说明
---|---|---
X-SFLL-Token | 否 | 前端授权token
X-SFLL-Admin-Token | 否 | 管理端授权token


### 错误码

服务器错误统一返回500, 客户端错误返回4xx

- Response示例
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

> 非生产环境Exception message为异常详细内容

