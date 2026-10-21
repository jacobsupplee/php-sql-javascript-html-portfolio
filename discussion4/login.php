<?php
//Loads the local database settings without putting credentials in this file
$config = require __DIR__ . '/../config.local.php';
$database = $config['publications'];

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
