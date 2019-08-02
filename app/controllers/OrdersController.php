<?php

use Phalcon\Mvc\Controller;

class OrdersController extends Controller
{
    public function index()
    {
        $items = [
            ['order_id' => 123, 'order_sn' => '196457865432'],
            ['order_id' => 234, 'order_sn' => '196457865875']
        ];

        return $this->response->setJsonContent(['items' => $items]);
    }

    public function post()
    {
        $order = ['order_id' => 123, 'order_sn' => '196457865432'];

        return $this->response->setStatusCode(201)->setJsonContent($order);
    }

    public function get(int $orderId)
    {
        $order = ['order_id' => $orderId, 'order_sn' => '196457865432'];

        return $this->response->setStatusCode(200)->setJsonContent($order);
    }

    public function put(int $orderId)
    {
        return $this->response->setStatusCode(200);
    }

    public function delete(int $orderId)
    {
        return $this->response->setStatusCode(204);
    }
}