## Phalcon 4

### 目录结构

```
- apidoc/                   API文档
- app/
  - config/                 配置
    - config.php            公共配置
    - cron.php              计划任务配置
    - services.php          Di注册服务
    - redis_constant.php    redis key. redis key统一在此定义, 避免冲突. 
  - controllers/            控制器
  - queue/                  消息队列
    - jobs/                 job处理类
      - QueueJob.php        队列任务处理入口
  - routes/                 Restful路由
    - app.php               默认路由配置
  - services/               公共业务逻辑
    - BaseService.php       基础类. 继承此类即可使用 self::di() 调用Di注册的服务.
    - UtilService.php       工具类
  - tasks/                  Cli任务
    - CronTask.php          Cron入口
- public/
  - index.php               Restful入口
- run                       Cli入口
```

### Restful

- RESTful指南参考 <a href="https://www.vinaysahni.com/best-practices-for-a-pragmatic-restful-api" target="_blank">Best Practices for Designing a Pragmatic RESTful API</a>

- 流程
  
  index.php -> routes -> controllers -> services
  
  - routes 为动态加载, 每次请求只会加载一个Module, 项目可以无限膨胀而不影响性能.
  - controllers 用于处理业务, 事务控制尽量放置在这里, 放置在services中容易出现事务嵌套的问题.
  - services 为可选, 用于封装公共的业务逻辑.

### Cli

### ApiDoc

- https://apidocjs.com/

```
// 生成文档
apidoc -i /path_to_project/ -o /path_to_apidoc_html/ -c /path_to_project/apidoc
```

### Queue

长耗时写操作/高并发写操作, 都应优先考虑使用队列处理. 队列任务的本质是执行 `app/services/` 中的静态方法.

- https://github.com/resque/php-resque

- 定义

```
// 生产. 队列名, Job, args => [string $serviceName, string $methodName, array $params, bool $transaction, int $retriedCount]
Resque::enqueue('queueName', 'QueueJob', $args);

// 消费. 队列名
(INTERVAL=1 COUNT=100 QUEUE=queueName php /path_to_project/app/queue/resque &> /dev/null &)

// 延迟队列
(INTERVAL=1 php /path_to_project/app/queue/resque-scheduler &> /dev/null &)
```

- 调用

  - 及时队列任务 UtilService::enqueue()
  - 延迟队列任务 UtilService::enqueueIn()
  - 定时队列任务 UtilService::enqueueAt()

### Cron

- https://github.com/SidRoberts/phalcon-cron
