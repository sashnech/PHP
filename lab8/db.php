<?php
declare(strict_types=1);

$host = getenv('FORUM_DB_HOST') ?: '127.0.0.1';
$port = getenv('FORUM_DB_PORT') ?: '3306';
$name = getenv('FORUM_DB_NAME') ?: 'lab4';
$user = getenv('FORUM_DB_USER') ?: 'root';
$password = getenv('FORUM_DB_PASSWORD') ?: '';

$pdo = new PDO(
    "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
    $user,
    $password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);
