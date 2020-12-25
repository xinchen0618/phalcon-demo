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
        // cache
        $page = UtilService::getQuery('page', '页码', '+int', 1);
        $perPage = UtilService::getQuery('per_page', '页大小', '+int', 12);
        $key = sprintf(REDIS_USERS, md5($page . $perPage));
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

        $this->cache->set($key, serialize($result), 60);

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
        $this->db->begin();

        $result = UserService::deleteUser($userId);

        $this->db->commit();

        return UtilService::uniformResponse($result);
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
