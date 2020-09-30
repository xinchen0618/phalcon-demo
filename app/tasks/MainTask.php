<?php

namespace app\tasks;

use app\services\UtilService;
use Phalcon\Cli\Task;
use Resque;

class MainTask extends Task
{
    /**
     * 写redis
     */
    public function setKeyAction(): void
    {
        for ($i =0; $i < 10; $i++) {
            $no = random_int(10000000000, 99999999999);
            $key = "user:{$no}";
            $this->redis->set($key, $no, 86400);
        }
    }

    /**
     * 搜索key
     */
    public function keysAction(): void
    {
        $keys = $this->redis->keys('*1*');
        var_export($keys);
    }

    /**
     * 扫描key
     */
    public function scanAction(): void
    {
        $it = NULL;
        do {
            // Scan for some keys
            $arr_keys = $this->redis->scan($it, '*1*');

            // Redis may return empty results, so protect against that
            if ($arr_keys !== FALSE) {
                foreach($arr_keys as $str_key) {
                    echo "Here is a key: $str_key\n";
                }
            }
            var_dump($it);
        } while ($it > 0);
        echo "No more keys to scan!\n";
    }

    /**
     * 查询key
     */
    public function getKeyAction(): void
    {
        $key = 'user:15866083251';
        $value = $this->redis->get($key);
        var_export($value);
    }

    /**
     * 入队及时异步任务
     */
    public function enqueueAction(): void
    {
        for ($i =0; $i < 10000; $i++) {
            UtilService::enqueue('UserService', 'postUsers', ['user_name' => random_int(100000, 999999)], true);
        }
    }

    /**
     * 入队延迟异步任务
     */
    public function enqueueInAction(): void
    {
        UtilService::enqueueIn(30, 'UserService', 'postUsers', ['user_name' => 'delay_' . random_int(100000, 999999)], true);
    }

    /**
     * 入队定时异步任务
     */
    public function enqueueAtAction(): void
    {
        $time = UtilService::getNextDeadline();
        UtilService::enqueueAt($time, 'UserService', 'postUsers', ['user_name' => 'timing_' . random_int(100000, 999999)], true);
    }

    /**
     * 失败异步任务重新入队
     */
    public function reEnqueueAction(): void
    {
        Resque::setBackend("{$this->config->redis->host}:{$this->config->redis->port}", $this->config->redisDbIndex->queue, $this->config->redis->auth);
        do {
            $job = $this->queueRedis->rPop('resque:failed');
            if ($job) {
                $job = json_decode($job, true);

                // 入队参数, 索引数组 [string $service, string $method, array $params, bool $transaction, int $retriedCount]
                $args = $job['payload']['args'][0];
                $args[4] = isset($args[4]) ? $args[4] + 1 : 1;  // 已重试次数

                Resque::enqueue($job['queue'], $job['payload']['class'], $args);
            }
        } while ($job);
    }

    /**
     * 执行异步任务
     * @param string $service
     * @param string $method
     * @param string $params JSON字符串
     * @param string $transaction true/false
     */
    public function doQueueAction(string $service, string $method, string $params = '', string $transaction = 'false'): void
    {
        $paramsArr = $params ? json_decode($params, true) : [];
        $transactionBool = 'true' == $transaction;  // 命令行参数为字符串
        $service = "\\app\\services\\{$service}";
        $result = $service::$method($paramsArr, $transactionBool);
        var_export($result);
    }
}
