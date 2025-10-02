<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class PedidoTest extends TestCase
{
    private function crearUsuarioDePrueba(PDO $pdo): int
    {
        $nombre = "usuarioPedido_" . uniqid();
        $email = $nombre . "@example.com";
        $passwordHash = password_hash("secret123", PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre,email,password,rol) VALUES (?,?,?,?)");
        $stmt->execute([$nombre, $email, $passwordHash, "usuario"]);
        return (int)$pdo->lastInsertId();
    }

    public function testCrearPedidoEnPendiente(): void
    {
        $pdo = $GLOBALS['pdo'];
        $this->assertInstanceOf(PDO::class, $pdo, "La conexión debe estar inicializada");

        $usuarioId = $this->crearUsuarioDePrueba($pdo);

        $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id,total,estado) VALUES (?,?,?)");
        $stmt->execute([$usuarioId, 100, "pendiente"]);
        $pedidoId = $pdo->lastInsertId();

        $stmt2 = $pdo->prepare("SELECT estado FROM pedidos WHERE id=?");
        $stmt2->execute([$pedidoId]);
        $estado = $stmt2->fetchColumn();

        $this->assertSame("pendiente", $estado);
    }

    public function testCancelarPedido(): void
    {
        $pdo = $GLOBALS['pdo'];
        $this->assertInstanceOf(PDO::class, $pdo, "La conexión debe estar inicializada");

        $usuarioId = $this->crearUsuarioDePrueba($pdo);

        $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id,total,estado) VALUES (?,?,?)");
        $stmt->execute([$usuarioId, 200, "pendiente"]);
        $pedidoId = $pdo->lastInsertId();

        // Cancelar pedido
        $stmt2 = $pdo->prepare("UPDATE pedidos SET estado='cancelado' WHERE id=? AND usuario_id=?");
        $stmt2->execute([$pedidoId, $usuarioId]);

        $stmt3 = $pdo->prepare("SELECT estado FROM pedidos WHERE id=?");
        $stmt3->execute([$pedidoId]);
        $estado = $stmt3->fetchColumn();

        $this->assertSame("cancelado", $estado);
    }
}
