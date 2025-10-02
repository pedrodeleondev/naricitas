<?php
declare(strict_types=1);

$pdo = null;

try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=naricitas;charset=utf8mb4',
        'root',
        'root',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    echo "Error de conexión a la BD en bootstrap: " . $e->getMessage() . PHP_EOL;
}

$GLOBALS['pdo'] = $pdo;
