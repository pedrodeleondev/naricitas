<?php
declare(strict_types=1);

$pdo = null;
$maxTries = 10;
$waitSeconds = 3;

while ($maxTries > 0) {
    try {
        $pdo = new PDO(
            'mysql:host=127.0.0.1;port=3306;dbname=naricitas;charset=utf8mb4',
            'root',
            'root',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
        break; // conexión exitosa
    } catch (PDOException $e) {
        echo "MySQL no disponible aún. Reintentando... (" . $e->getMessage() . ")\n";
        $maxTries--;
        sleep($waitSeconds);
    }
}

if (!$pdo) {
    echo "❌ No se pudo conectar a la BD después de varios intentos\n";
}

$GLOBALS['pdo'] = $pdo;
