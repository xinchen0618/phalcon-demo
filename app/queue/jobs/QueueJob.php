<?php

use Phalcon\Di;

class QueueJob
{
    public function perform(): void
    {
        // Work work work
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
    }
}
