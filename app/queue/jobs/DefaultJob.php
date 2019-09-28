<?php

use Phalcon\Di;

class DefaultJob
{
    public function perform()
    {
        $method = $this->args['method'] ?? '';
        if (!$method) {
            return false;
        }
        $args = $this->args['args'] ?? [];

        try {
            QueueService::$method(...$args);
        } catch (\Throwable $e) {
            // 重试5次，重试间隔不断延长
            $tried = $this->args['tried'] ?? 1;  // 第几次执行
            if ($tried <= 3) {
                // 重试
                sleep($tried ** 5);
                $redisConfig = Di::getDefault()->getConfig()->redis;
                Resque::setBackend("{$redisConfig->host}:{$redisConfig->port}", 3);
                Resque::enqueue('default', 'DefaultJob', ['method' => $method, 'args' => $args, 'tried' => $tried + 1]);
            } else {
                // 失败，提醒人工干预
                error_log("消息队列Job执行失败. method: {$method}" . ($args ? ', args: ' . json_encode($args) : ''));
            }
        }
    }
}