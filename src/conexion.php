<?php
$host = getenv("DB_HOST") ?: "127.0.0.1";
$db   = getenv("DB_NAME") ?: "naricitas";
$user = getenv("DB_USER") ?: "root";
$pass = getenv("DB_PASS") ?: "";
$port = getenv("DB_PORT") ?: "3306";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $GLOBALS['pdo'] = $pdo;
} catch (Exception $e) {
    $pdo = null;
    $GLOBALS['pdo'] = null;
}
