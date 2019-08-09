<?php
/*
 * Modified: prepend directory path of current file, because of this file own different ENV under between Apache and command line.
 * NOTE: please remove this comment.
 */

return new \Phalcon\Config([
    'mysql' => [
        'host'       => '127.0.0.1',
        'port'       => 3306,
        'username'   => 'root',
        'password'   => 'cx654321',
        'dbname'     => 'test',
        'charset'    => 'utf8',
    ]
]);
