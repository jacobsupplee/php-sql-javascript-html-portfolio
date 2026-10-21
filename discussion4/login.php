<?php
// Local database settings are kept outside version control.
$config = require __DIR__ . '/../config.local.php';
$database = $config['publications'];

$host = $database['host'];
$port = $database['port'];
$data = $database['database'];
$user = $database['username'];
$pass = $database['password'];
$chrs = $database['charset'];

$attr = "mysql:host=$host;port=$port;dbname=$data;charset=$chrs";
$opts = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
