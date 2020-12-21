<?php

namespace app\services;

use Phalcon\Di;

class BaseService
{
    /**
     * 获取di注册的服务
     * @param string $service
     * @return mixed
     */
    public static function di(string $service)
    {
        return Di::getDefault()->get($service);
    }
}
