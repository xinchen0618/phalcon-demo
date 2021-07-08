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
     * 手动执行队列任务
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

        if ($transactionBool) {
            $this->db->begin();
        }

        $result = $service::$method($paramsArr);

        if ($transactionBool) {
            $this->db->commit();
        }

        var_export($result);
    }

    /**
     * 失败队列重新入队
     */
    public function reEnqueueAction(): void
    {
        Resque::setBackend("{$this->config->redis->host}:{$this->config->redis->port}", $this->config->redis->index->queue, $this->config->redis->auth);
        do {
            $job = $this->queueRedis->rPop('resque:failed');
            if ($job) {
                $job = json_decode($job, true);
                Resque::enqueue($job['queue'], $job['payload']['class'], $job['payload']['args'][0]);
            }
        } while ($job);
    }

    /**
     * 入队及时队列任务
     * @param int $counts
     * @throws \Exception
     */
    public function enqueueAction(int $counts = 1000): void
    {
        for ($i = 0; $i < $counts; $i++) {
            UtilService::enqueue('UserService', 'postUsers', ['user_name' => random_int(100000, 999999)], true);
        }
    }

    /**
     * 入队延迟队列任务
     */
    public function enqueueInAction(): void
    {
        UtilService::enqueueIn(30, 'UserService', 'postUsers', ['user_name' => 'delay_' . random_int(100000, 999999)], true);
    }

    /**
     * 入队定时队列任务
     */
    public function enqueueAtAction(): void
    {
        $time = strtotime(date('Y-m-d H:i:00')) + 60;
        UtilService::enqueueAt($time, 'UserService', 'postUsers', ['user_name' => 'timing_' . random_int(100000, 999999)], true);
    }

    public function positionInitAction(int $counts = 10): void
    {
        for ($i = 0; $i < $counts; $i++) {
            $this->db->insertAsDict('t_users', ['user_name' => random_int(100000, 999999)]);
            $userId = $this->db->lastInsertId();
            $this->db->updateAsDict('t_users', ['position' => $userId * 1024], "user_id = {$userId}");
        }
    }

    public function dragPositionAction(int $userId, int $prevUserId): void
    {
        // 首位
        if (!$prevUserId) {
            $first = $this->db->fetchOne('SELECT position, user_id FROM t_users ORDER BY position LIMIT 1');
            if ($first['user_id'] != $userId) {
                $position = $first['position'] / 2;
                $this->db->updateAsDict('t_users', ['position' => $position], "user_id = {$userId}");
            }

            return;
        }

        $prev = $this->db->fetchOne("SELECT user_id, position FROM t_users WHERE user_id = {$prevUserId}");
        $next = $this->db->fetchOne("SELECT user_id, position FROM t_users WHERE position > {$prev['position']} ORDER BY position LIMIT 1");

        // 两元素间
        if ($prev && $next) {
            $position = ($prev['position'] + $next['position']) / 2;
            var_export($prev);
            echo "\n";
            var_export($next);
            echo "\n";
            var_export($position);
            echo "\n";
            $this->db->updateAsDict('t_users', ['position' => $position], "user_id = {$userId}");

            return;
        }

        // 末尾
        if ($prev && !$next) {
            $last = $this->db->fetchOne('SELECT user_id FROM t_users ORDER BY user_id DESC LIMIT 1');
            $position = ($prev['position'] + ($last['user_id'] + 1) * 1024) / 2;
            $this->db->updateAsDict('t_users', ['position' => $position], "user_id = {$userId}");
        }
    }

}
