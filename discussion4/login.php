<?php
//Loads the local database settings without putting credentials in this file
$config = require __DIR__ . '/../config.local.php';

if (!is_array($config) || !isset($config['publications']) ||
    !is_array($config['publications'])) {
    throw new RuntimeException('The Publications database configuration is missing or invalid.');
}

$database = $config['publications'];
$requiredSettings = ['host', 'port', 'database', 'username', 'password', 'charset'];

foreach ($requiredSettings as $setting) {
    if (!array_key_exists($setting, $database) ||
        (!is_string($database[$setting]) && !is_int($database[$setting]))) {
        throw new RuntimeException('A required Publications database setting is missing or invalid.');
    }
}

if ($database['host'] === '' || $database['database'] === '' ||
    $database['charset'] === '' ||
    filter_var($database['port'], FILTER_VALIDATE_INT) === false ||
    (int) $database['port'] < 1 || (int) $database['port'] > 65535) {
    throw new RuntimeException('The Publications database connection settings are invalid.');
}

//Stores the connection settings for the Publications database
$host = $database['host'];
$port = $database['port'];
$data = $database['database'];
$user = $database['username'];
$pass = $database['password'];
$chrs = $database['charset'];

//Builds the PDO connection string
$attr = "mysql:host=$host;port=$port;dbname=$data;charset=$chrs";

//Configures PDO to throw errors, return associative arrays, and use real prepared statements
$opts = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
