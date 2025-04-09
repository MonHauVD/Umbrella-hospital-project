<?php
//add to git irgnore
return [
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'doantotnghiep',
    'username'  => 'root',
    'password'  => '123456',
    'charset'   => 'utf8',
    'options'   => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ],
    'database_frontend' => 'nextpost'
];