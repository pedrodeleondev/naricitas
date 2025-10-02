<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class UsuarioTest extends TestCase
{
    public function testInsertarYRecuperarUsuario(): void
    {
        $pdo = $GLOBALS['pdo'];
        $this->assertInstanceOf(PDO::class, $pdo, "La conexión debe estar inicializada");

        $nombre = "usuarioTest_" . uniqid();
        $email = $nombre . "@example.com";
        $passwordPlano = "secret123";
        $passwordHash = password_hash($passwordPlano, PASSWORD_BCRYPT);

        // Insertar usuario
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre,email,password,rol) VALUES (?,?,?,?)");
        $stmt->execute([$nombre, $email, $passwordHash, "usuario"]);
        $userId = $pdo->lastInsertId();

        // Recuperar usuario
        $stmt2 = $pdo->prepare("SELECT * FROM usuarios WHERE id=?");
        $stmt2->execute([$userId]);
        $usuario = $stmt2->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($usuario, "El usuario debería existir en la BD");
        $this->assertSame($email, $usuario['email']);
        $this->assertTrue(password_verify($passwordPlano, $usuario['password']));
    }
}
