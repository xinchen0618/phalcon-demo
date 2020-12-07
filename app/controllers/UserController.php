<?php

namespace app\controllers;

use app\services\UserService;
use app\services\UtilService;
use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;

class UserController extends Controller
{
    public function getUsers(): Response
    {
        $key = 'users';
        $result = $this->cache->get($key);
        if ($result) {
            return UtilService::successResponse(200, unserialize($result));
        }

        $result = UtilService::getPageItems([
            'select' => 'u.user_id, u.user_name, uc.counts',
            'from' => 'users AS u JOIN user_counts AS uc ON u.user_id = uc.user_id',
            'where' => '1',
            'orderBy' => 'u.user_id DESC'
        ]);

        $this->cache->set($key, serialize($result));
        $this->cache->expireAt($key, UtilService::getNextDeadline(7));

        return UtilService::successResponse(200, $result);
    }

    public function postUsers(): Response
    {
        UtilService::speedLimit();

        $json = UtilService::getJsonBody(['user_name:用户名:string:+']);

        $this->db->begin();

        $userId = UserService::postUsers($json);

        $this->db->commit();

        return UtilService::successResponse(200, ['user_id' => $userId]);
    }

    public function deleteUsersById(int $userId): Response
    {
        $result = UserService::deleteUser($userId);

        return UtilService::response($result);
    }

    public function putUsersDeleted(): Response
    {
        $json = UtilService::getJsonBody(['user_counts:删除数量:+int:+']);

        $users = $this->db->fetchAll("SELECT user_id FROM users WHERE user_id <= {$json['user_counts']} AND is_deleted = 0");
        foreach ($users as $user) {
            UtilService::enqueue('UserService', 'softDeleteUser', $user);
        }

        return UtilService::successResponse(204);
    }
}
