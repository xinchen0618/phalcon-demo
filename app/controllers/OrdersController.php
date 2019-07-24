<?php

use Phalcon\Mvc\Controller;

class OrdersController extends Controller
{
    public function index(): void
    {
        echo "list \n";
    }

    public function add(): void
    {
        echo 'add';
    }

    public function detail(int $orderId): void
    {
        echo "detail: $orderId";
    }

    public function update(int $orderId): void
    {
        echo "update: {$orderId}";
    }

    public function delete(int $orderId): void
    {
        echo "delete: {$orderId}";
    }
}