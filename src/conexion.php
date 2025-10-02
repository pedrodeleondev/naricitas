<?php
$host = "127.0.0.1";
$db   = "naricitas";
$user = "root";
$pass = getenv("DB_PASS") ?: ""; 
$port = "3306";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $GLOBALS['pdo'] = $pdo;
} catch (Exception $e) {
    $pdo = null;
    $GLOBALS['pdo'] = null;
}
