<?php
/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

use Phalcon\Mvc\Micro\Collection as MicroCollection;

$user = new MicroCollection();
$user->setHandler(new UserController());
$user->setPrefix('/user/v1');

$user->get('/users', 'getUsers');
$user->post('/users', 'postUsers');
$user->delete('/users/{user_id:[1-9]\d*}', 'deleteUsersById');

$app->mount($user);
