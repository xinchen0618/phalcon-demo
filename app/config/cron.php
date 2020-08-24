<?php
declare(strict_types=1);

$di->set(
    'cron',
    function () {
        $cron = new \Sid\Phalcon\Cron\Manager();

        // 失败任务重新入队
        $cron->add(
            new \Sid\Phalcon\Cron\Job\Phalcon(
                '* * * * *',
                [
                    'task' => 'Main',
                    'action' => 'reEnqueue'
                ]
            )
        );

        return $cron;
    }
);
