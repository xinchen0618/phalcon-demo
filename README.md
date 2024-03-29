## Phalcon 4

### 目录结构

```
- apidoc/                   API文档
- app/
  - config/                 配置
    - config.php            公共配置. 环境配置 config_{$_SERVER['RUNTIME_ENV']}.php 会覆盖同键名公共配置. dev环境配置不参与版本控制.
    - cron_prod.php         生产环境计划任务配置. 计划任务配置按环境分开.
    - services.php          Di注册服务
    - redis_constant.php    redis key统一在此定义, 避免冲突. 
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

### 环境定义

  环境变量 `RUNTIME_ENV` 指定执行环境.
  
  - `dev`       开发环境
  - `testing`   测试环境
  - `stage`     预发布环境
  - `prod`      生产环境

### RESTful

- RESTful指南参考 <a href="https://www.vinaysahni.com/best-practices-for-a-pragmatic-restful-api" target="_blank">Best Practices for Designing a Pragmatic RESTful API</a>

- 流程
  
  `public/index.php` -> `app/routes/` -> `app/controllers/` [-> `app/services/`]
  
  - `app/routes/` 为动态加载, 每次请求只会加载一个Module, 项目可以无限膨胀而不影响性能.
  - `app/controllers/` 用于处理业务, 事务控制尽量放置在这里, 放置在 `app/services/` 中容易出现事务嵌套的问题.
  - `app/services/` 用于封装公共的业务逻辑, 为可选.

### ApiDoc

- https://apidocjs.com/

  ```
  // 生成文档
  apidoc -i /path_to_project/ -o /path_to_apidoc_html/ -c /path_to_project/apidoc/apidoc.json
  ```

### Queue

队列用于实现执行异步任务, 长耗时写操作和高并发写操作, 都应优先考虑使用队列处理.

当需要让某个任务异步执行，只需要把这个任务包装成一个`app/services/` 静态方法，使用 `UtilService::enqueue()` 去执行这个方法就可以了. 

无特殊要求任务统一进入 `universal` 队列, 此队列拥有最多的worker待命. 数量大且优先级低的任务进入其他队列, 它们共享较少数量的worker.

队列任务执行异常有重试机制, 重试间隔为斐波那契数列, 总计持续约20小时. 队列任务要注意使用事务和做幂等校验.

- https://github.com/resque/php-resque

- 定义

  ```
  // 生产. $args => [string $serviceName, string $methodName, array $params, bool $transaction, int $retriedCount]
  Resque::enqueue($queueName, 'QueueJob', $args);
  
  // 消费, 注意定义环境
  (INTERVAL=1 COUNT=100 QUEUE=universal RUNTIME_ENV=xxx php /path_to_project/app/queue/resque &> /dev/null &)
  (INTERVAL=1 COUNT=5 QUEUE=* RUNTIME_ENV=xxx php /path_to_project/app/queue/resque &> /dev/null &)
  (INTERVAL=1 RUNTIME_ENV=xxx php /path_to_project/app/queue/resque-scheduler &> /dev/null &)
  
  // 关闭队列
  kill -QUIT $(ps aux | grep -v grep | grep /queue/resque | awk '{print $2}')
  ```

- 使用

  - 入队及时队列任务 `UtilService::enqueue(string $serviceName, string $methodName, array $params = [], bool $transaction = false, string $queue = 'universal')`
  - 入队延迟队列任务 `UtilService::enqueueIn(int $delay, string $serviceName, string $methodName, array $params = [], bool $transaction = false, string $queue = 'universal')`
  - 入队定时队列任务 `UtilService::enqueueAt(int $timestamp, string $serviceName, string $methodName, array $params = [], bool $transaction = false, string $queue = 'universal')`

### Cli

### Cron

- https://github.com/SidRoberts/phalcon-cron

- 启动

  ```
  // crontab -e, 注意定义环境
  * * * * * RUNTIME_ENV=xxx /usr/bin/php /path_to_project/run Cron
  ```

### 性能

- API执行超过3秒将报警
- 队列任务执行超过10秒将报警
- Task执行超过30秒将报警
