### API风格

- 风格 

  RESTful, 指南: <a href="https://www.vinaysahni.com/best-practices-for-a-pragmatic-restful-api" target="_blank">Best Practices for Designing a Pragmatic RESTful API</a> 【重要】

- 数据类型

  API统一使用严格数据类型校验


### Endpoint

- API地址

  完整API地址: 域名 + Module + 版本 + API Name

- API请求示例:
  ```
  curl -X POST \
    https://api.example.com/comment/v1/comments \
    -H 'Content-Type: application/json' \
    -H 'X-Token: vuomdg39uevkajtnismb68r407' \
    -H 'X-Source: example-miniprogram' \
    -d '{
      "order_id": 62,
      "content": "服务很满意"
    }'
  ```

- 域名

  环境    | 域名
  ---    |---
  生产    | https://api.example.com

- Module

  项目由多Module组成, 各Module均独立维护.

  Module                                | 说明
  ---                                   |---
  [user](#api-user)                     | 用户API

- 版本

  Major[.Minor][.Revision], 比如 /v1, /v1.1

  API出现向下不兼容且旧版仍需继续使用的情况, ~~比如不升级的旧版APP,~~ 新增Minor或Revision版本号. 业务出现结构性变化, 新增Major版本号.


### HTTP Headers

- 自定义专用Headers

  Header        | 是否必填        | 说明
  ---           |---             |---
  X-Token       | 否             | 登录授权token
  X-Source      | 是             | 请求来源

- 请求来源X-Source

  值                    | 说明
  ---                   |---
  example-miniprogram   | 示例小程序


### 错误码

服务器错误统一返回500, 客户端错误返回4xx. 返回实体包含两个字段: status-错误状态, message-错误描述.

- Response示例

  ```
  HTTP/1.1 404 Not Found
  {
      "status": "ResourceNotFound",
      "message": "您请求的资源不存在"
  }
  ```

- 公共错误码

  Http Status Code  | status                | message
  ---               |---                    |---
  500               | Exception             | 服务异常, 请稍后重试 (非生产环境为异常详情)
  404               | ResourceNotFound      | 您请求的资源不存在 (请求不存在的API返回此错误)
  400               | InvalidSource         | 无效请求来源
  400               | InvalidParam          | 参数不正确 (类型校验不通过, 见message详情)
  400               | EmptyParam            | 参数不得为空 (缺少必填参数或必填参数传空值, 见message详情)
  429               | SpeedLimit            | 手快了, 请稍后~~

- 业务错误码

  详见各API Error说明.

