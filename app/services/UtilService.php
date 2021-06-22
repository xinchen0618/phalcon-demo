<?php

namespace app\services;

use Phalcon\Http\Response;
use Resque;
use ResqueScheduler;

class UtilService extends BaseService
{
    /**
     * 获取请求实体
     * @param array $params 参数
     *  ['paramKey:paramName:valueType:regulate'] 键:键名:类型:校验, 校验: +必填不可空, *选填可为空, ?选填不可空
     * @return array
     */
    public static function getJsonBody(array $params): array
    {
        $json = self::di('request')->getJsonRawBody(true) ?: [];

        $filterParams = [];
        if ($params) {
            foreach ($params as $param) {
                [$paramKey, $paramName, $valueType, $regulate] = explode(':', $param);

                $required = true;  // 必填
                $allowEmpty = false;  // 可否为空
                if ('+' === $regulate) {
                    $required = true;
                    $allowEmpty = false;
                } elseif ('*' === $regulate) {
                    $required = false;
                    $allowEmpty = true;
                } elseif ('?' === $regulate) {
                    $required = false;
                    $allowEmpty = false;
                }

                if ($required && !isset($json[$paramKey])) {
                    self::errorResponse(400, 'EmptyParam', "{$paramName}不得为空");
                }
                if (isset($json[$paramKey])) {
                    $filterParams[$paramKey] = self::filterParam($paramName, $json[$paramKey], $valueType, $allowEmpty);
                }
            }
        }

        return $filterParams;
    }

    /**
     * 获取query参数
     * @param string $paramKey 键
     * @param string $paramName 键名
     * @param mixed  $valueType 值类型
     * @param mixed  $defaultValue 默认值
     * @return mixed
     */
    public static function getQuery(string $paramKey, string $paramName, $valueType, $defaultValue = null)
    {
        $value = self::di('request')->getQuery($paramKey);
        if (null === $value || '' === $value) {
            if (null !== $defaultValue) {
                return $defaultValue;
            }

            self::errorResponse(400, 'EmptyParam', "{$paramName}不得为空");
        }

        $allowEmpty = null !== $defaultValue;
        return self::filterParam($paramName, $value, $valueType, $allowEmpty);
    }

    /**
     * 验证参数
     * @param string $paramName
     * @param mixed  $paramValue
     * @param mixed  $valueType
     *  'literal' - 文本, 去除首尾空格
     *  'int' - 整数
     *  '+int' - 正整数
     *  '!-int' - 非负整数
     *  'string' - 字符串, 去除Tag, 去除首尾空格
     *  [] - 枚举
     *  'image' - 图片
     *  'phone' - 国内电话号码
     *  'email' - 邮件地址
     *  'array' - 数组
     *  'images' - 图片数组, 返回Json字符串
     * @param bool   $allowEmpty 是否允许为空
     * @return mixed
     */
    public static function filterParam(string $paramName, $paramValue, $valueType, bool $allowEmpty = false)
    {
        /* 文本, 去除首尾空格 */
        if ('literal' === $valueType) {
            if (!(is_numeric($paramValue) || is_string($paramValue))) {
                self::errorResponse(400, 'InvalidParam', "{$paramName}不正确");
            }
            $paramValue = trim($paramValue);
            if ('' === $paramValue && !$allowEmpty) {
                self::errorResponse(400, 'EmptyParam', "{$paramName}不得为空");
            }

            return $paramValue;
        }

        /* 整型 */
        if ('int' === $valueType) {
            $paramValue = self::filterParam($paramName, $paramValue, 'literal', $allowEmpty);  // 转为字符串比较, 整型比较会有类型强转的问题
            if ('' === $paramValue) {
                return 0;  // 兼容, 空视为0
            }

            $paramValueInt = (int)$paramValue;
            if ($paramValue != (string)$paramValueInt) {  // 兼容小数位0, 不可使用绝对等于判断
                self::errorResponse(400, 'InvalidParam', "{$paramName}不正确");
            }

            return $paramValueInt;
        }

        /* 正整型 */
        if ('+int' === $valueType) {
            $intValue = self::filterParam($paramName, $paramValue, 'int', $allowEmpty);
            if ($intValue <= 0) {
                self::errorResponse(400, 'InvalidParam', "{$paramName}不正确");
            }

            return $intValue;
        }

        /* 非负整型 */
        if ('!-int' === $valueType) {
            $intValue = self::filterParam($paramName, $paramValue, 'int', $allowEmpty);
            if ($intValue < 0) {
                self::errorResponse(400, 'InvalidParam', "{$paramName}不正确");
            }

            return $intValue;
        }

        /* 字符串, 去除Tag, 去除首尾空格 */
        if ('string' === $valueType) {
            $paramValue = self::filterParam($paramName, $paramValue, 'literal', $allowEmpty);
            $value = trim(strip_tags($paramValue));
            if ('' === $value && !$allowEmpty) {
                self::errorResponse(400, 'EmptyParam', "{$paramName}不得为空");
            }

            return $value;
        }

        /* 枚举, 支持数字与字符串混合枚举 */
        if (strpos($valueType, '[') === 0) {
            $valueType = json_decode($valueType, true);
            $paramValue = self::filterParam($paramName, $paramValue, 'literal', $allowEmpty);
            foreach ($valueType as $valueItem) {
                if ($paramValue == (string)$valueItem) {  // 兼容小数位0, 不可使用绝对等于判断
                    return $valueItem;
                }
            }

            self::errorResponse(400, 'InvalidParam', "{$paramName}不正确");
        }

        /* 图片 */
        if ('image' === $valueType) {
            $paramValue = self::filterParam($paramName, $paramValue, 'literal', $allowEmpty);
            if ('' === $paramValue) {
                return '';
            }

            if (!self::isImage($paramValue)) {
                self::errorResponse(400, 'InvalidParam', "{$paramName}不正确");
            }

            return $paramValue;
        }

        /* 国内电话号码 */
        if ('phone' === $valueType) {
            $paramValue = self::filterParam($paramName, $paramValue, 'literal', $allowEmpty);
            if ('' === $paramValue) {
                return '';
            }

            if (!preg_match('/^1[3456789]\d{9}$/', $paramValue)
                && !preg_match('/^(([0+]\d{2,3}-)?(0\d{2,3})-)(\d{7,8})(-(\d{3,}))?$/', $paramValue)) {
                self::errorResponse(400, 'InvalidParam', "{$paramName}不正确");
            }

            return $paramValue;
        }

        /* 邮箱 */
        if ('email' === $valueType) {
            $paramValue = self::filterParam($paramName, $paramValue, 'literal', $allowEmpty);
            if ('' === $paramValue) {
                return '';
            }

            if (!filter_var($paramValue, FILTER_VALIDATE_EMAIL)) {
                self::errorResponse(400, 'InvalidParam', "{$paramName}不正确");
            }

            return $paramValue;
        }

        /* 金额 */
        if ('money' === $valueType) {
            $paramValue = self::filterParam($paramName, $paramValue, 'literal', $allowEmpty);
            if ('' === $paramValue) {
                return '0.00';
            }

            if (!is_numeric($paramValue) || $paramValue < 0 || $paramValue != sprintf('%.2F', $paramValue)) {  // 兼容小数位0, 不可使用绝对等于判断
                self::errorResponse(400, 'InvalidParam', "{$paramName}不正确");
            }

            return sprintf('%.2F', $paramValue);
        }

        /* 数组 */
        if ('array' === $valueType) {
            if (!is_array($paramValue)) {
                self::errorResponse(400, 'InvalidParam', "{$paramName}不正确");
            }
            if (!$paramValue && !$allowEmpty) {
                self::errorResponse(400, 'EmptyParam', "{$paramName}不得为空");
            }

            return $paramValue;
        }

        /* 图片数组, 返回json字符串 */
        if ('images' === $valueType) {
            $paramValue = self::filterParam($paramName, $paramValue, 'array', $allowEmpty);

            $cleanImages = [];
            foreach ($paramValue as $image) {
                if (self::isImage($image)) {
                    $cleanImages[] = $image;
                }
            }

            return $cleanImages ? json_encode($cleanImages) : '';
        }

        self::errorResponse(400, 'UndefinedValueType', "未知数据类型: {$paramName}");  // 后端错误
    }

    /**
     * 错误返回并结束程序
     * @param int    $statusCode
     * @param string $status
     * @param string $message
     */
    public static function errorResponse(int $statusCode, string $status, string $message): void
    {
        self::di('response')->setStatusCode($statusCode)->setJsonContent([
            'status' => $status,
            'message' => $message
        ])->send();
        exit;
    }

    /**
     * 成功返回
     * @param int   $statusCode
     * @param array $content
     * @return Response
     */
    public static function successResponse(int $statusCode, array $content = []): Response
    {
        $response = self::di('response')->setStatusCode($statusCode);
        if ($content) {
            $response->setJsonContent($content);
        }

        return $response;
    }

    /**
     * 返回
     * @param array $result
     *      成功返回-[int $statusCode, array $content = []],
     *      错误返回-[int $statusCode, string $status, string $message]
     * @return Response
     */
    public static function uniformResponse(array $result): Response
    {
        /* 错误返回 */
        if ($result[0] >= 400) {
            return self::di('response')->setStatusCode($result[0])->setJsonContent([
                'status' => $result[1],
                'message' => $result[2]
            ]);
        }

        /* 成功返回 */
        $response = self::di('response')->setStatusCode($result[0]);
        if (isset($result[1]) && is_array($result[1])) {
            $response->setJsonContent($result[1]);
        }

        return $response;
    }

    /**
     * 频率限制
     * @param string $key
     * @param int    $interval
     */
    public static function speedLimit(string $key = '', int $interval = 3): void
    {
        if (!$key) {
            $path = $_SERVER['REQUEST_URI'];
            if (strpos($path, '?')) {
                $path = strstr($path, '?', true);
            }
            $key = "{$_SERVER['REQUEST_METHOD']}:{$path}:" . self::di('session')->getId();
        }

        $flag = self::di('redis')->set($key, 1, ['nx', 'ex' => $interval]);
        if (!$flag) {
            self::errorResponse(429, 'SpeedLimit', '手快了, 请稍后~~');
        }
    }

    /**
     * 获取分页数据
     * @param array $query
     *  [
     *      'select' => string,
     *      'from' => string,
     *      'where' => string,
     *      'groupBy' => string,
     *      'having' => string,
     *      'bindParams' => string,
     *      'orderBy' => string
     *  ]
     *
     * @return array
     *  [
     *      'page' => int,
     *      'per_page' => int,
     *      'total_pages' => int,
     *      'total_counts' => int,
     *      'items' => array
     *  ]
     */
    public static function getPageItems(array $query): array
    {
        $page = self::getQuery('page', '页码', '+int', 1);
        $perPage = self::getQuery('per_page', '页大小', '+int', 12);

        $bindParams = $query['bindParams'] ?? [];
        if (isset($query['groupBy'])) {
            $query['where'] .= " GROUP BY {$query['groupBy']}";
        }
        if (isset($query['having'])) {
            $query['where'] .= " HAVING {$query['having']}";
        }

        if (isset($query['groupBy']) || stripos($query['select'], 'DISTINCT') === 0) {
            $countSql = "SELECT COUNT(*) FROM (SELECT {$query['select']} FROM {$query['from']} WHERE {$query['where']}) AS t";
        } else {
            $countSql = "SELECT COUNT(*) FROM {$query['from']} WHERE {$query['where']}";
        }
        $counts = self::di('db')->fetchColumn($countSql, $bindParams);
        $totalPages = ceil($counts / $perPage);

        $result = [
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
            'total_counts' => $counts,
            'items' => []
        ];
        if ($page <= $totalPages) {
            $offset = ($page - 1) * $perPage;
            $sql = "SELECT {$query['select']} FROM {$query['from']} WHERE {$query['where']}";
            if (isset($query['orderBy'])) {
                $sql .= " ORDER BY {$query['orderBy']}";
            }
            $sql .= " LIMIT {$offset}, {$perPage}";

            $result['items'] = self::di('db')->fetchAll($sql, 2, $bindParams);
        }

        return $result;
    }

    /**
     * 文件是否为图片
     * @param string $src
     * @return bool
     */
    public static function isImage(string $src): bool
    {
        $whitelistImageTypes = [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP];  // 图片格式白名单, ImageType https://www.php.net/manual/en/function.exif-imagetype
        $mimeType = @exif_imagetype($src);

        return in_array($mimeType, $whitelistImageTypes);
    }

    /**
     * 入队及时队列任务
     * @param string $serviceName 服务名
     * @param string $methodName 服务静态方法名
     * @param array  $params 静态方法参数
     * @param bool   $transaction 是否开启事务
     * @param string $queue 队列名
     * @return string|boolean
     */
    public static function enqueue(string $serviceName, string $methodName, array $params = [], bool $transaction = false, string $queue = 'universal')
    {
        if (null === Resque::$redis) {
            $redis = self::di('config')->redis;
            Resque::setBackend("{$redis->host}:{$redis->port}", $redis->index->queue, $redis->auth);
        }

        return Resque::enqueue($queue, 'QueueJob', [$serviceName, $methodName, $params, $transaction]);
    }

    /**
     * 入队延迟队列任务
     * @param int    $delay 延迟时间(秒)
     * @param string $serviceName 服务名
     * @param string $methodName 服务静态方法名
     * @param array  $params 静态方法参数
     * @param bool   $transaction 是否开启事务
     * @param string $queue 队列名
     * @return void
     */
    public static function enqueueIn(int $delay, string $serviceName, string $methodName, array $params = [], bool $transaction = false, string $queue = 'universal'): void
    {
        if (null === Resque::$redis) {
            $redis = self::di('config')->redis;
            Resque::setBackend("{$redis->host}:{$redis->port}", $redis->index->queue, $redis->auth);
        }

        ResqueScheduler::enqueueIn($delay, $queue, 'QueueJob', [$serviceName, $methodName, $params, $transaction]);
    }

    /**
     * 入队定时队列任务
     * @param int    $timestamp 执行时间, 时间戳
     * @param string $serviceName 服务名
     * @param string $methodName 服务静态方法名
     * @param array  $params 静态方法参数
     * @param bool   $transaction 是否开启事务
     * @param string $queue 队列名
     */
    public static function enqueueAt(int $timestamp, string $serviceName, string $methodName, array $params = [], bool $transaction = false, string $queue = 'universal'): void
    {
        if (null === Resque::$redis) {
            $redis = self::di('config')->redis;
            Resque::setBackend("{$redis->host}:{$redis->port}", $redis->index->queue, $redis->auth);
        }

        ResqueScheduler::enqueueAt($timestamp, $queue, 'QueueJob', [$serviceName, $methodName, $params, $transaction]);
    }
}
