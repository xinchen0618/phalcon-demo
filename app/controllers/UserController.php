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
}
