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

            $workCost = microtime(true) - $workStartTime;
            if ($workCost > UtilService::di('config')->slowQueueCost) {
                error_log("慢Queue警告! 执行耗时: {$workCost}秒. Queue: " . var_export($this->args, true) . " \n");
            }

        } catch (Throwable $e) {    /* Retry */
            error_log($e->getMessage() . " \n" . $e->getTraceAsString() . " \n");

            $this->args[4] = isset($this->args[4]) ? $this->args[4] + 1 : 1;    // retriedCount
            if ($this->args[4] <= 23) {   // 持续约20小时
                ResqueScheduler::enqueueIn(UtilService::fib($this->args[4]), $this->queue, 'QueueJob', $this->args);
            } else {
                unset($this->args[4]);
                error_log('队列任务执行失败: ' . var_export($this->args, true) . " \n");
            }
        }
    }
}
