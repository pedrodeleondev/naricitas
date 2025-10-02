<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/conexion.php';

final class UsuarioTest extends TestCase
{
    public function testInsertarYRecuperarUsuario(): void
    {
        $pdo = $GLOBALS['pdo'];

        $nombre = "usuarioTest_" . uniqid();
        $email = $nombre . "@example.com";
        $passwordPlano = "secret123";
        $passwordHash = password_hash($passwordPlano, PASSWORD_BCRYPT);

        // insertar usuario
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre,email,password,rol) VALUES (?,?,?,?)");
        $stmt->execute([$nombre, $email, $passwordHash, "usuario"]);
        $userId = $pdo->lastInsertId();

        // recuperar usuario
        $stmt2 = $pdo->prepare("SELECT * FROM usuarios WHERE id=?");
        $stmt2->execute([$userId]);
        $usuario = $stmt2->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($usuario, "El usuario debería existir en la BD");
        $this->assertSame($email, $usuario['email']);
        $this->assertTrue(password_verify($passwordPlano, $usuario['password']));
    }
}
