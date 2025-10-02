<?php
$host = "127.0.0.1";      // Servidor
$db   = "naricitas";      // Nombre de tu base de datos
$user = "root";           // Usuario
$pass = "";               // Contraseña
$port = "3306";           // Puerto MySQL

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $GLOBALS['pdo'] = $pdo;
} catch (Exception $e) {
    $pdo = null;
    $GLOBALS['pdo'] = null;
}
