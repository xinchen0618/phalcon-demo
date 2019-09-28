<?php

use Phalcon\Di;

/**
 * Class QueueService
 * 消息队列事务统一在此Service中控制
 */
class QueueService
{
    public static function addUser(string $userName): bool
    {
        Di::getDefault()->getDb()->insertAsDict('users', ['user_name' => $userName . '222']);

        return 'success';
    }
}