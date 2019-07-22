<?php
/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

use Phalcon\Mvc\Micro\Collection as MicroCollection;

$accounts = new MicroCollection();
$accounts->setHandler(new AccountsController());
$accounts->setPrefix('/accounts/v1');

$accounts->put('/login', 'login');

$app->mount($accounts);