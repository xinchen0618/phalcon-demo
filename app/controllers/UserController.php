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
//        $page = UtilService::getQuery('page', '页码', '+int', 1);
//        $perPage = UtilService::getQuery('per_page', '页大小', '+int', 12);
//        $key = sprintf(REDIS_USERS, md5($page . $perPage));
//        $result = $this->cache->get($key);
//        if ($result) {
//            return UtilService::successResponse(200, unserialize($result));
//        }

        $result = UtilService::getPageItems([
            'select' => 'u.user_id, u.user_name, u.money, u.created_at, u.updated_at, uc.counts',
            'from' => 't_users AS u JOIN t_user_counts AS uc ON u.user_id = uc.user_id',
            'where' => '1',
            'orderBy' => 'u.user_id DESC'
        ]);

//        $this->cache->set($key, serialize($result), 60);

        return UtilService::successResponse(200, $result);
    }

    public function getUsersByOffset(): Response
    {
        return UtilService::successResponse(200, ['next_offset' => -1, 'items' => []]);
    }

    public function postUsers(): Response
    {
//        UtilService::speedLimit();

        $json = UtilService::getJsonBody(['counts:数量:+int:*']);
        $counts = $json['counts'] ?? 100;

//        $this->db->begin();
        for ($i = 0; $i < $counts; $i++) {
            $user = ["user_name" => random_int(111111111, 999999999)];
            $userId = UserService::postUsers($user);
        }

//        $this->db->commit();

        return UtilService::successResponse(200, ['user_id' => $userId]);
    }

    public function deleteUsersById(int $userId): Response
    {
        $this->db->begin();

        $result = UserService::deleteUser($userId);

        $this->db->commit();

        return UtilService::uniformResponse($result);
    }
}
