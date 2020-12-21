<?php

namespace app\services;

class UserService extends BaseService
{
    /**
     * 添加用户
     * @param array $user ['user_name' => '']
     * @return int
     */
    public static function postUsers(array $user): int
    {
        $userId = self::di('db')->fetchColumn("SELECT user_id FROM users WHERE user_name = '{$user['user_name']}'");
        if (!$userId) {
            self::di('db')->insertAsDict('users', ['user_name' => $user['user_name']]);
            $userId = self::di('db')->lastInsertId();
        }
        $sql = "INSERT INTO user_counts (user_id, counts) VALUES ({$userId}, 1) ON DUPLICATE KEY UPDATE counts = counts + 1";
        self::di('db')->execute($sql);

        return $userId;
    }

    /**
     * @param int $userId
     * @return array 成功-[204], 失败-[$statusCode, $status, $message]
     */
    public static function deleteUser(int $userId): array
    {
        $user = self::di('db')->fetchOne("SELECT user_id FROM users WHERE user_id = {$userId}");
        if (!$user) {
            return [404, 'UserNotFound', '用户不存在'];
        }

        self::di('db')->delete('users', "user_id = {$userId}");
        self::di('db')->delete('user_counts', "user_id = {$userId}");

        return [204];
    }

    /**
     * @param array $user
     * @throws \Exception
     */
    public static function softDeleteUser(array $user): void
    {
        self::di('db')->updateAsDict('users', ['is_deleted' => 1, 'deleted_nanotime' => UtilService::nanotime()], "user_id = {$user['user_id']} AND is_deleted = 0");
    }
}
