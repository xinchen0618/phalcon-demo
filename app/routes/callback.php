<?php

/**
 * 第三方回调相关API
 */

use app\controllers\CallbackController;
use Phalcon\Mvc\Micro\Collection as MicroCollection;

$callback = new MicroCollection();
$callback->setHandler(new CallbackController());
$callback->setPrefix('/callback');

/**
 * @api {post} /v1/order/alipay 订单支付宝支付回调
 * @apiName callback_post_order/alipay
 * @apiVersion 1.0.0
 * @apiGroup callback
 * @apiPermission none
 * @apiDescription 订单支付宝支付回调, https://opendocs.alipay.com/open/204/105301
 *
 */
$callback->post('/v1/order/alipay', 'postOrderAlipay');

$app->mount($callback);
