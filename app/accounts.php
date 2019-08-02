<?php
/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

use Phalcon\Mvc\Micro\Collection as MicroCollection;

$accounts = new MicroCollection();
$accounts->setHandler(new AccountsController());
$accounts->setPrefix('/account/v1');

$accounts->post('/login', 'login');

$app->mount($accounts);