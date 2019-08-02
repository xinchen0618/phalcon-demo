<?php
/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

use Phalcon\Mvc\Micro\Collection as MicroCollection;

$orders = new MicroCollection();
$orders->setHandler(new OrdersController());
$orders->setPrefix('/order/v1');

/**
 * @api {get} /orders 订单列表
 * @apiGroup Orders
 * @apiDescription 获取订单列表
 *
 * @apiSuccess {String} firstname Firstname of the User.
 * @apiSuccess {String} lastname  Lastname of the User.
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "firstname": "John",
 *       "lastname": "Doe"
 *     }
 *
 * @apiError UserNotFound The id of the User was not found.
 *
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */
$orders->get('/orders', 'index');
$orders->post('/orders', 'post');
$orders->get('/orders/{orderId:[0-9]+}', 'get');
$orders->put('/orders/{orderId:[0-9]+}', 'put');
$orders->delete('/orders/{orderId:[0-9]+}', 'delete');

$app->mount($orders);