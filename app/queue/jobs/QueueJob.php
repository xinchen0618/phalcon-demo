<?php

use app\services\UtilService;

class QueueJob
{
    public function perform(): void
    {
        try {   /* Work work work */
            [$service, $method] = $this->args;
            $params = $this->args[2] ?? [];
            $transaction = $this->args[3] ?? false;

            // Slow queue
            $workStartTime = microtime(true);

            if ($transaction) {
                UtilService::di('db')->begin();
            }

            $service = "\\app\\services\\{$service}";
            $service::$method($params);

            if ($transaction) {
                UtilService::di('db')->commit();
            }

            $workDuration = microtime(true) - $workStartTime;
            if ($workDuration > UtilService::di('config')->slowQueueDuration) {
                error_log("慢Queue警告! 执行耗时: {$workDuration}秒. Queue: " . var_export($this->args, true) . " \n");
            }

        } catch (Throwable $e) {    /* Retry */
            error_log($e->getMessage() . " \n" . $e->getTraceAsString() . " \n");

            $this->args[4] = isset($this->args[4]) ? $this->args[4] + 1 : 1;    // retriedCount
            if ($this->args[4] <= 24) {   // 持续约25小时
                ResqueScheduler::enqueueIn($this->args[4] ** 3, $this->queue, 'QueueJob', $this->args);
            } else {
                unset($this->args[4]);
                error_log('队列任务执行失败: ' . var_export($this->args, true) . " \n");
            }
        }
    }
}
