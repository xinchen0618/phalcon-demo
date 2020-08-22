<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;

class UserController extends Controller
{
    public function getUsers(): Response
    {
        $result = UtilService::getPageItems([
            'select' => 'u.user_id, u.user_name, uc.counts',
            'from' => 'users AS u JOIN user_counts AS uc ON u.user_id = uc.user_id',
            'where' => '1',
            'orderBy' => 'u.user_id DESC'
        ]);

        return UtilService::successResponse(200, $result);
    }

    public function postUsers(): Response
    {
        $json = UtilService::getJsonBody(['user_name:用户名:string:+']);

        $this->db->begin();

        $userId = $this->db->fetchColumn("SELECT user_id FROM users WHERE user_name = '{$json['user_name']}'");
        if (!$userId) {
            $this->db->insertAsDict('users', ['user_name' => $json['user_name']]);
            $userId = (int) $this->db->lastInsertId();
        }
        $sql = "INSERT INTO user_counts (user_id, counts) VALUES ({$userId}, 1) ON DUPLICATE KEY UPDATE counts = counts + 1";
        $this->db->execute($sql);

        $this->db->commit();

        return UtilService::successResponse(200, ['user_id' => $userId]);
    }

    public function deleteUsersById(int $userId): Response
    {
        $result = UserService::deleteUser($userId);

        return UtilService::response($result);
    }
}
