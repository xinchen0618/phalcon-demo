<?php

use Phalcon\Di;

class QueueJob
{
    public function perform(): void
    {
        try {   /* Work work work */
            [$service, $method] = $this->args;
            $params = $this->args[2] ?? [];
            $transaction = $this->args[3] ?? false;

            if ($transaction) {
                $db = Di::getDefault()->get('db');
                $db->begin();
            }

            $service = "\\app\\services\\{$service}";
            $service::$method($params);

            if ($transaction) {
                $db->commit();
            }

        } catch (Throwable $e) {    /* Retry */
            error_log($e->getMessage() . " \n" . $e->getTraceAsString() . " \n");

            $this->args[4] = isset($this->args[4]) ? $this->args[4] + 1 : 1;    // retriedCount
            if ($this->args[4] <= 20) {   // 持续约12小时
                ResqueScheduler::enqueueIn($this->args[4] ** 3, $this->queue, 'QueueJob', $this->args);
            } else {
                unset($this->args[4]);
                error_log('异步任务执行失败: ' . var_export($this->args, true) . " \n");
            }
        }
    }
}
