<?php

use Phalcon\Mvc\Controller;

class AccountsController extends Controller
{
    public function login()
    {
        $user = ['user_id' => 123, 'user_name' => 'Clarified'];

        return $this->response->setStatusCode(201)->setJsonContent($user);
    }
}