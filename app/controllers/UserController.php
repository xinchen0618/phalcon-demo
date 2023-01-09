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
        $result = UtilService::getPageItems([
            'select' => 'u.user_id, u.user_name, u.money, u.created_at, u.updated_at, uc.counts',
            'from' => 't_users AS u JOIN t_user_counts AS uc ON u.user_id = uc.user_id',
            'where' => '1',
            'orderBy' => 'u.user_id DESC'
        ]);

        return UtilService::successResponse(200, $result);
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
