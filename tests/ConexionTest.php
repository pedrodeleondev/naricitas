<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/conexion.php';

final class ConexionTest extends TestCase
{
    public function testConexionEsValida(): void
    {
        $pdo = $GLOBALS['pdo'];
        $this->assertInstanceOf(PDO::class, $pdo, "La conexión PDO debería estar inicializada");
    }

    public function testConexionUsaUtf8(): void
    {
        $pdo = $GLOBALS['pdo'];
        $stmt = $pdo->query("SHOW VARIABLES LIKE 'character_set_connection'");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertTrue(
            in_array($row['Value'], ['utf8', 'utf8mb4']),
            "La conexión debería estar usando UTF8 o UTF8MB4"
        );
    }
}
