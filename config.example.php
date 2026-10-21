<?php
// Copy this file to config.local.php and enter your local database credentials.
// Keep config.local.php out of version control.

//Stores connection settings for each database used by the coursework
return [
    'publications' => [
        'host' => 'localhost',
        'port' => '3306',
        'database' => 'publications',
        'username' => '',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'cmsc340' => [
        'host' => 'localhost',
        'port' => '3306',
        'database' => 'cmsc340',
        'username' => '',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
];
