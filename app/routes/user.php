<?php

use app\controllers\UserController;
use Phalcon\Mvc\Micro\Collection as MicroCollection;

$user = new MicroCollection();
$user->setHandler(new UserController());
$user->setPrefix('/user');

/**
 * @api {get} /v1/users 获取用户列表[Deprecated]
 * @apiName user_get_users
 * @apiVersion 1.0.0
 * @apiGroup user
 * @apiPermission X-Token
 * @apiDescription 获取用户列表, page/per_page分页方式
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
$user->get('/v1/users', 'getUsers');

/**
 * @api {get} /v1.1/users 获取用户列表
 * @apiName user_get_users
 * @apiVersion 1.1.0
 * @apiGroup user
 * @apiPermission X-Token
 * @apiDescription 获取用户列表, offset/limit分页方式
 *
 * @apiParam (Query参数) {String}   [q='']        搜索, 昵称
 * @apiParam (Query参数) {Integer}  [offset=0]    起始id, 0-首页
 * @apiParam (Query参数) {Integer}  [limit=12]    返回记录数
 *
 * @apiSuccess {Integer}    next_offset         下一页起始id, -1-没有下一页了
 * @apiSuccess {Object[]}   items               列表
 * @apiSuccess {Integer}    items.user_id       用户id
 * @apiSuccess {String}     items.nickname      昵称
 * @apiSuccessExample 成功响应示例
 *  HTTP/1.1 200 OK
 *  {
 *      "next_offset": 1,
 *      "items": [
 *          {
 *              "user_id": 988,
 *              "nickname": "小乔"
 *          }
 *      ]
 *  }
 */
$user->get('/v1.1/users', 'getUsersByOffset');

/**
 * @api {post} /v1/users 添加用户
 * @apiName user_post_users
 * @apiVersion 1.0.0
 * @apiGroup user
 * @apiPermission X-Token
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
$user->post('/v1/users', 'postUsers');

/**
 * @api {delete} /v1/users/:user_id 删除用户
 * @apiName user_delete_users_user_id
 * @apiVersion 1.0.0
 * @apiGroup user
 * @apiPermission X-Token
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

/**
 * @api {put} /v1/users/deleted 批量删除用户
 * @apiName user_put_users_deleted
 * @apiVersion 1.0.0
 * @apiGroup user
 * @apiPermission X-Token
 * @apiDescription 批量删除用户, 软删除
 *
 * @apiParam (Entity参数) {Integer} user_counts               删除数量
 * @apiParamExample {json} 请求实体示例
 *  {
 *      "user_counts": 100
 *  }
 *
 * @apiSuccessExample 成功响应示例
 *  HTTP/1.1 204 No Content
 */
$user->put('/v1/users/deleted', 'putUsersDeleted');

$app->mount($user);
