<?php

namespace app\tasks;

use Phalcon\Cli\Task;


class BaseTask extends Task
{
    public $taskStartTime;

    public function initialize()
    {
        $this->taskStartTime = microtime(true);
    }

    public function afterExecuteRoute($dispatcher)
    {
        // 慢Task警告
        $taskDuration = microtime(true) - $this->taskStartTime;
        if ($taskDuration > $this->config->slowTaskDuration) {
            $task = $this->dispatcher->getTaskName();
            $action = $this->dispatcher->getActionName();
            $params = $this->dispatcher->getParams();
            $params = $params ? implode(' ', $params) : '';
            error_log("慢Task警告! 执行耗时: {$taskDuration}秒. Task: {$task} {$action} {$params} \n");
        }
    }
}
