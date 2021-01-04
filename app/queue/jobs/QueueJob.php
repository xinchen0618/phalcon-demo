<?php

use app\services\UtilService;

class QueueJob
{
    public $workStartTime;

    public function perform(): void
    {
        try {   /* Work work work */
            [$service, $method] = $this->args;
            $params = $this->args[2] ?? [];
            $transaction = $this->args[3] ?? false;

            if ($transaction) {
                UtilService::di('db')->begin();
            }

            $service = "\\app\\services\\{$service}";
            $service::$method($params);

            if ($transaction) {
                UtilService::di('db')->commit();
            }

        } catch (Throwable $e) {    /* Retry */
            error_log($e->getMessage() . " \n" . $e->getTraceAsString() . " \n");

            $this->args[4] = isset($this->args[4]) ? $this->args[4] + 1 : 1;    // retriedCount
            if ($this->args[4] <= 24) {   // 持续约25小时
                ResqueScheduler::enqueueIn($this->args[4] ** 3, $this->queue, 'QueueJob', $this->args);
            } else {
                unset($this->args[4]);
                error_log('异步任务执行失败: ' . var_export($this->args, true) . " \n");
            }
        }
    }

    public function setUp(): void
    {
        // ... Set up environment for this job
        $this->workStartTime = microtime(true);
    }

    public function tearDown(): void
    {
        // 慢异步任务警告
        $workDuration = microtime(true) - $this->workStartTime;
        if ($workDuration > UtilService::di('config')->slowQueueDuration) {
            error_log("慢异步任务警告! 执行耗时: {$workDuration}秒. 任务内容: " . var_export($this->args, true) . " \n");
        }
    }
}
