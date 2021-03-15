<?php

namespace app\controllers;

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;

class CallbackController extends Controller
{
    public function postOrderAlipay(): Response
    {
        return $this->response->setContentType('text/plain')->setContent('success');
    }
}
