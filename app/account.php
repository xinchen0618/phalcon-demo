<?php
/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

use Phalcon\Mvc\Micro\Collection as MicroCollection;

$accounts = new MicroCollection();
$accounts->setHandler('AccountController', true);
$accounts->setPrefix('/account/v1');

$accounts->post('/login', 'postLogin');

$app->mount($accounts);