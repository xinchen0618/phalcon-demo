<?php
declare(strict_types=1);

namespace app\tasks;

use Phalcon\Cli\Task;

class CronTask extends Task
{
    public function mainAction(): void
    {
        $this->cron->runInBackground();
    }
}
