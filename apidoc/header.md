### API风格

- 风格 

  RESTful, 指南: <a href="https://www.vinaysahni.com/best-practices-for-a-pragmatic-restful-api" target="_blank">Best Practices for Designing a Pragmatic RESTful API</a> 【重要】

- 数据类型

  API统一使用严格数据类型校验 

### Endpoint

- API地址

  完整API地址: 域名 + Module + 版本 + API Method

- API请求示例:
```
curl -X POST \
  https://api.sfll.net/comment/v1/comments \
  -H 'Content-Type: application/json' \
  -H 'X-Sfll-Token: vuomdg39uevkajtnismb68r407' \
  -H 'X-Sfll-From: sfll-miniprogram' \
  -d '{
    "order_id": 62,
    "content": "服务很满意"
  }'测试 | https://api-testing.sfll.xyz
```

- 域名

环境 | 域名
---|---
生产 | https://api.sfll.xyz

- Module

项目由多Module组成, Module均单独维护版本

Module | 版本 | 说明
---|---|---
[account](#api-account)                 | v1    | 账户API           

### HTTP Headers

- 自定义专用Headers

Header | 是否必填 | 说明
---|---|---
X-Sfll-Token | 否 | 登录授权token

- 请求来源X-Sfll-From

值 | 说明
--- | ---
sfll-miniprogram | 师傅来了小程序

### 错误码

服务器错误统一返回500, 客户端错误返回4xx. 返回实体包含两个字段: status-错误状态, message-错误描述

- Response示例
```
HTTP/1.1 404 Not Found
{
    "status": "ResourceNotFound",
    "message": "您请求的资源不存在"
}
```

- 公共错误码

如下错误码被多API使用
 
Http Status Code | status | message
---|---|---
500 | Exception | 服务异常，请稍后重试 (非生产环境为异常详细信息)
404 | ResourceNotFound | 您请求的资源不存在 (请求不存在的API返回此错误)

- 业务错误码

API返回的特定错误码, 详见各API Error说明

