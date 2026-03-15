<?php

$env = require dirname(__DIR__) . '/.env.php';

$connection = new mysqli(
    $env['DB_HOST'],
    $env['DB_USER'],
    $env['DB_PASS'],
    $env['DB_NAME']
);
$connection->set_charset('utf8mb4');

if ($connection->connect_error)
    die('Connection failed: ' . $connection->connect_error);
