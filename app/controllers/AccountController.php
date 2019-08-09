<?php

use Phalcon\Mvc\Controller;

class AccountController extends Controller
{
    public function postLogin()
    {
        $user = ['user_id' => 123, 'user_name' => 'Clarified'];

        return $this->response->setStatusCode(201)->setJsonContent($user);
    }
}