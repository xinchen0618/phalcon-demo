<?php
/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

use Phalcon\Mvc\Micro\Collection as MicroCollection;

$orders = new MicroCollection();
$orders->setHandler(new OrderController());
$orders->setPrefix('/order/v1');

/**
 * @api {get} /orders 订单列表
 * @apiGroup Order
 * @apiDescription 获取订单列表
 *
 * @apiSuccess {String} firstname Firstname of the User.
 * @apiSuccess {String} lastname  Lastname of the User.
 *
 * @apiSuccessExample Success-Response:
 *  HTTP/1.1 200 OK
 *  {
 *    "firstname": "John",
 *    "lastname": "Doe"
 *  }
 */
$orders->get('/orders', 'getOrders');
$orders->post('/orders', 'postOrders');
$orders->get('/orders/{orderId:[0-9]+}', 'getOrdersById');
$orders->put('/orders/{orderId:[0-9]+}', 'putOrdersById');
$orders->delete('/orders/{orderId:[0-9]+}', 'deleteOrdersById');

$app->mount($orders);