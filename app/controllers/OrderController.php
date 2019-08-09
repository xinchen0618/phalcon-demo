<?php

use Phalcon\Mvc\Controller;

class OrderController extends Controller
{
    public function getOrders()
    {
        $items = [
            ['order_id' => 123, 'order_sn' => '196457865432'],
            ['order_id' => 234, 'order_sn' => '196457865875']
        ];

        return $this->response->setJsonContent(['items' => $items]);
    }

    public function postOrders()
    {
        $order = ['order_id' => 123, 'order_sn' => '196457865432'];

        return $this->response->setStatusCode(201)->setJsonContent($order);
    }

    public function getOrdersById(int $orderId)
    {
        $order = ['order_id' => $orderId, 'order_sn' => '196457865432'];

        return $this->response->setStatusCode(200)->setJsonContent($order);
    }

    public function putOrdersById(int $orderId)
    {
        return $this->response->setStatusCode(200);
    }

    public function deleteOrdersById(int $orderId)
    {
        return $this->response->setStatusCode(204);
    }
}