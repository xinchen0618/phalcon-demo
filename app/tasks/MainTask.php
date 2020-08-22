<?php
declare(strict_types=1);

use Phalcon\Cli\Task;

class MainTask extends Task
{
    /**
     * 写redis
     * @throws Exception
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
     * 执行队列任务
     * @param string $service
     * @param string $method
     * @param string $params JSON字符串
     * @param string $transaction 'true'/'false', 命令行参数只能是字符串
     */
    public function queueAction(string $service, string $method, string $params = '', string $transaction = 'false'): void
    {
        $params = $params ? json_decode($params, true) : [];
        $transactionBool = 'true' == $transaction;

        echo '$service: ';
        var_export($service);
        echo "\n";

        echo '$method: ';
        var_export($method);
        echo "\n";

        echo '$params: ';
        var_export($params);
        echo "\n";

        echo '$transactionBool: ';
        var_export($transactionBool);
        echo "\n";

        $service::$method($params, $transactionBool);
    }

    /**
     * 入队
     * @throws Exception
     */
    public function enqueueAction(): void
    {
        for ($i =0; $i < 10000; $i++) {
            UtilService::enqueue('universal', 'UserService', 'postUsers', ['user_name' => random_int(100000, 999999)], true);
        }
    }

    /**
     * 失败任务重新入队
     */
    public function reEnqueueAction(): void
    {
        Resque::setBackend('127.0.0.1:6379', 1);
        do {
            $job = $this->queueRedis->rPop('resque:failed');
            if ($job) {
                $job = json_decode($job, true);
                Resque::enqueue($job['queue'], $job['payload']['class'], $job['payload']['args'][0]);
            }
        } while ($job);
    }

    /**
     * 延迟队列任务
     */
    public function delayQueueAction(): void
    {
        Resque::setBackend('127.0.0.1:6379', 1);

        $args = ['UserService', 'postUsers', ['user_name' => 'delay_' . random_int(100000, 999999)], true];
        ResqueScheduler::enqueueIn(30, 'universal', 'QueueJob', $args);
    }

    public function deleteUserAction(): void
    {
        $result = UserService::deleteUser(9);
        var_export($result);
    }

}
