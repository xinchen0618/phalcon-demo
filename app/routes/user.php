<?php

/**
 * 用户API
 */

use app\controllers\UserController;
use Phalcon\Mvc\Micro\Collection as MicroCollection;

$user = new MicroCollection();
$user->setHandler(new UserController());
$user->setPrefix('/user/v1');

/**
 * @api {get} /users 获取用户列表
 * @apiName userGetUsers
 * @apiVersion 1.0.0
 * @apiGroup user
 * @apiPermission token
 * @apiDescription 获取用户列表
 *
 * @apiDeprecated
 *
 * @apiParam (Query参数) {String}   [q='']          搜索, 昵称
 * @apiParam (Query参数) {Integer}  [page=1]        页码
 * @apiParam (Query参数) {Integer}  [per_page=12]   每页记录数
 *
 * @apiSuccess {Integer} page                   页码
 * @apiSuccess {Integer} per_page               每页记录数
 * @apiSuccess {Integer} total_pages            总页数
 * @apiSuccess {Integer} total_counts           总记录数
 * @apiSuccess {Object[]} items                 列表
 * @apiSuccess {Integer} items.user_id           用户id
 * @apiSuccess {String} items.nickname          昵称
 * @apiSuccessExample 成功响应示例
 *  HTTP/1.1 200 OK
 *  {
 *      "page": 1,
 *      "per_page": 12,
 *      "total_pages": 1,
 *      "total_counts": 1,
 *      "items": [
 *          {
 *              "user_id": 988,
 *              "nickname": "小乔"
 *          }
 *      ]
 *  }
 */
$user->get('/users', 'getUsers');

/**
 * @api {post} /users 添加用户
 * @apiName userPostUsers
 * @apiVersion 1.0.0
 * @apiGroup user
 * @apiPermission user:PostUsers
 * @apiDescription 添加用户
 *
 * @apiParam (Entity参数) {String} user_name           用户名
 * @apiParamExample {json} 请求实体示例
 *  {
 *      "user_name": "zhangsan"
 *  }
 *
 * @apiSuccess (Success 201) {Integer} user_id         用户id
 * @apiSuccessExample 成功响应示例
 *  HTTP/1.1 201 Created
 *  {
 *      "user_id": 3
 *  }
 */
$user->post('/users', 'postUsers');

/**
 * @api {delete} /users/:user_id 删除用户
 * @apiName userDeleteUsersById
 * @apiVersion 1.0.0
 * @apiGroup user
 * @apiPermission user:DeleteUsersById
 * @apiDescription 删除用户, 物理删除
 *
 * @apiParam (Path参数) {Integer} user_id         分类id
 *
 * @apiSuccessExample 成功响应示例
 *  HTTP/1.1 204 No Content
 *
 * @apiError (Error 400) UserOrderNotEmpty 用户订单不为空
 */
$user->delete('/v1/users/{user_id:[1-9]\d*}', 'deleteUsersById');

$app->mount($user);
