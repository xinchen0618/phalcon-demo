## Phalcon

### Restful

### Cli

### ApiDoc

- https://apidocjs.com/

```
// 生成文档
apidoc -i /path_to_project/ -o /path_to_apidoc_html/ -c /path_to_project/apidoc
```

### Queue

- https://github.com/resque/php-resque

```
// 生产. 队列名, Job, args => [string $service, string $method, array $params, bool $transaction]
Resque::enqueue('queueName', 'QueueJob', $args);

// 消费. 队列名
INTERVAL=1 COUNT=100 QUEUE=queueName php /path_to_project/app/queue/resque

// 延迟任务
php /path_to_project/app/queue/resque-scheduler
```
