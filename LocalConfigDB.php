<?php
return [
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'doantotnghiep',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8',
    'options'   => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ],
    'database_frontend' => 'nextpost'
];