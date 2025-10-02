<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "conexion.php";
include "jwt.php";

$usuario = null;
if (isset($_COOKIE['token'])) {
    $usuario = validarJWT($_COOKIE['token'], $secret);
}
if (!$usuario) { 
    header("Location: login.php"); 
    exit; 
}

$msg = "";
$showAlert = false;

// --- ACCIONES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedido_id = $_POST['pedido_id'];

    // Admin → marcar como enviado
    if ($usuario['rol'] === 'admin' && isset($_POST['enviar'])) {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado='enviado' WHERE id=?");
        $stmt->execute([$pedido_id]);
        $msg = "📦 Pedido #$pedido_id marcado como enviado.";
        $showAlert = true;
    }

    // Admin → cancelar el envío (volver a pendiente)
    if ($usuario['rol'] === 'admin' && isset($_POST['cancel_envio'])) {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado='pendiente' WHERE id=? AND estado='enviado'");
        $stmt->execute([$pedido_id]);
        if ($stmt->rowCount() > 0) {
            $msg = "↩️ Pedido #$pedido_id regresado a pendiente.";
        } else {
            $msg = "⚠️ No se pudo cancelar el envío (quizá no estaba en 'enviado').";
        }
        $showAlert = true;
    }

    // Usuario → cancelar pedido (solo si pendiente)
    if ($usuario['rol'] === 'usuario' && isset($_POST['cancelar'])) {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado='cancelado' 
                               WHERE id=? AND estado='pendiente' AND usuario_id=?");
        $stmt->execute([$pedido_id, $usuario['id']]);
        if ($stmt->rowCount() > 0) {
            $msg = "❌ Pedido #$pedido_id cancelado correctamente.";
        } else {
            $msg = "⚠️ No se pudo cancelar el pedido (puede que ya esté enviado o no sea tuyo).";
        }
        $showAlert = true;
    }
}

// --- CARGAR PEDIDOS ---
if ($usuario['rol'] === 'admin') {
    $stmt = $pdo->query("SELECT p.id, u.nombre, p.fecha, p.estado, p.total 
                         FROM pedidos p 
                         JOIN usuarios u ON u.id = p.usuario_id 
                         ORDER BY p.fecha DESC");
} else {
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE usuario_id=? ORDER BY fecha DESC");
    $stmt->execute([$usuario['id']]);
}
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedidos</title>
    <style>
        body{margin:0;font-family:sans-serif;background:#b87c4c;color:#fff;}
        .pedido{background:#fff;color:#000;padding:15px;margin:20px auto;width:80%;border-radius:8px;}
        table{width:100%;border-collapse:collapse;margin-top:10px;}
        th,td{padding:10px;border:1px solid #ccc;text-align:center;}
        form{display:inline;}
        button{padding:6px 10px;margin:2px;border:none;border-radius:4px;cursor:pointer;}
        .btn-enviar{background:#2d7a2d;color:white;}
        .btn-enviar:hover{background:#1b501b;}
        .btn-cancelar{background:#c0392b;color:white;}
        .btn-cancelar:hover{background:#96281b;}
        .btn-pendiente{background:#e67e22;color:white;}
        .btn-pendiente:hover{background:#ca6f1e;}
        h2{text-align:center;}
        /* ALERTA flotante */
        .alerta{position:fixed;bottom:20px;right:20px;background:#fff;color:#000;padding:15px;border-radius:8px;
                box-shadow:0px 4px 12px rgba(0,0,0,0.2);font-weight:bold;z-index:9999;
                animation:fadein 0.5s,fadeout 0.5s 3s;}
        @keyframes fadein{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
        @keyframes fadeout{from{opacity:1;transform:translateY(0);}to{opacity:0;transform:translateY(20px);}}
    </style>
</head>
<body>
<?php include "header.php"; ?>
<h2>Mis pedidos 🛒</h2>

<?php foreach($pedidos as $p): ?>
    <div class="pedido">
        <h3>Pedido #<?php echo $p['id']; ?> (<?php echo $p['estado']; ?>)</h3>
        <p><b>Fecha:</b> <?php echo $p['fecha']; ?> | <b>Total:</b> $<?php echo $p['total']; ?></p>
        <?php if($usuario['rol'] === 'admin' && isset($p['nombre'])): ?>
            <p><b>Usuario:</b> <?php echo $p['nombre']; ?></p>
        <?php endif; ?>

        <table>
            <tr><th>Producto</th><th>Cantidad</th><th>Precio Unitario</th></tr>
            <?php
            $stmt2 = $pdo->prepare("SELECT i.cantidad, i.precio_unitario, pr.nombre 
                                    FROM pedido_items i 
                                    JOIN productos pr ON pr.id = i.producto_id 
                                    WHERE i.pedido_id=?");
            $stmt2->execute([$p['id']]);
            $items = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            foreach ($items as $it) {
                echo "<tr><td>".$it['nombre']."</td><td>".$it['cantidad']."</td><td>$".$it['precio_unitario']."</td></tr>";
            }
            ?>
        </table>

        <!-- Botones de acción -->
        <?php if($usuario['rol'] === 'admin'): ?>
            <?php if($p['estado'] === 'pendiente'): ?>
                <form method="post">
                    <input type="hidden" name="pedido_id" value="<?php echo $p['id']; ?>">
                    <button type="submit" name="enviar" class="btn-enviar">Marcar como Enviado</button>
                </form>
            <?php elseif($p['estado'] === 'enviado'): ?>
                <form method="post">
                    <input type="hidden" name="pedido_id" value="<?php echo $p['id']; ?>">
                    <button type="submit" name="cancel_envio" class="btn-pendiente">Cancelar Envío (Volver a Pendiente)</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <?php if($usuario['rol'] === 'usuario' && $p['estado'] === 'pendiente'): ?>
            <form method="post">
                <input type="hidden" name="pedido_id" value="<?php echo $p['id']; ?>">
                <button type="submit" name="cancelar" class="btn-cancelar">Cancelar Pedido</button>
            </form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<!-- Mini alerta -->
<?php if($showAlert): ?>
<div class="alerta" id="alerta"><?php echo $msg; ?></div>
<script>
setTimeout(function(){ var alerta=document.getElementById("alerta"); if(alerta) alerta.remove(); }, 4000);
</script>
<?php endif; ?>
</body>
</html>
