<?php

use Phalcon\Di;

class UserService
{
    /**
     * 添加用户
     * @param array $params ['user_name' => '']
     * @return int
     * @throws Exception
     */
    public static function postUsers(array $params): int
    {
        $db = Di::getDefault()->get('db');

        $userName = random_int(100000, 999999);
        $userId = $db->fetchColumn("SELECT user_id FROM users WHERE user_name = '{$userName}'");
        if (!$userId) {
            $db->insertAsDict('users', ['user_name' => $userName]);
            $userId = $db->lastInsertId();
        }
        $sql = "INSERT INTO user_counts (user_id, counts) VALUES ({$userId}, 1) ON DUPLICATE KEY UPDATE counts = counts + 1";
        $db->execute($sql);

        return $userId;
    }

    /**
     * @param int $userId
     * @return array 成功-[204], 失败-[$statusCode, $status, $message]
     */
    public static function deleteUser(int $userId): array
    {
        $db = Di::getDefault()->get('db');

        $user = $db->fetchOne("SELECT user_id FROM users WHERE user_id = {$userId}");
        if (!$user) {
            return [404, 'UserNotFound', '用户不存在'];
        }

        $db->delete('users', "user_id = {$userId}");
        $db->delete('user_counts', "user_id = {$userId}");

        return [204];
    }
}
