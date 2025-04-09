<?php
//add to git irgnore
return [
    'driver'    => 'mysql',
    'host'      => 'ngat',
    'database'  => 'doantotnghiep',
    'username'  => 'root',
    'password'  => '12345',
    'charset'   => 'utf8',
    'options'   => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ],
    'database_frontend' => 'nextpost'
];