<?php
//add to git irgnore
$hostname = php_uname('n');

$hostMap = [
    'VIETDUNG-PC' => 'localhost',
    'DAT-LAPTOP' => 'localhost',
    'MSI' => 'localhost',
    'LAPTOP-ND4MU73N' => 'localhost',
];
$defaultMap = 'localhost';

$hostUsername = [
    'VIETDUNG-PC' => 'root',
    'DAT-LAPTOP' => 'root',
    'MSI' => 'ngat',
    'LAPTOP-ND4MU73N' => 'mysql',
];
$defaultUsername = 'root';

$hostPassword = [
    'VIETDUNG-PC' => '',
    'DAT-LAPTOP' => '123456',
    'MSI' => '12345aA@*',
    'LAPTOP-ND4MU73N' => '12345',
];
$defaultPassword = '';

return [
    'driver'    => 'mysql',
    'host'      => $hostMap[$hostname] ?? $defaultMap,
    'database'  => 'doantotnghiep',
    'username'  => $hostUsername[$hostname] ?? $defaultUsername,
    'password'  => $hostPassword[$hostname] ?? $defaultPassword,
    'charset'   => 'utf8',
    'options'   => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ],
    'database_frontend' => 'nextpost'
];