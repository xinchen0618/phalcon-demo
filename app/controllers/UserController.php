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
            'from' => 't_users AS u JOIN t_user_counts AS uc ON u.user_id = uc.user_id',
            'where' => '1',
            'orderBy' => 'u.user_id DESC'
        ]);

        $this->cache->set($key, serialize($result), 60);

        return UtilService::successResponse(200, $result);
    }

    public function getUsersByOffset(): Response
    {
        return UtilService::successResponse(200, ['next_offset' => -1, 'items' => []]);
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

        $this->db->updateAsDict('t_users', ['is_deleted' => 1, 'deleted_time' => time()], "user_id <= {$json['user_counts']} AND is_deleted = 0");

        return UtilService::successResponse(204);
    }
}
