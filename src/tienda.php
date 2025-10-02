<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "conexion.php";
include "jwt.php";

$usuario = null;
if (isset($_COOKIE['token'])) {
    $usuario = validarJWT($_COOKIE['token'], $secret);
}

$msg = "";
$showAlert = false;

// --- AGREGAR PRODUCTO ---
if ($usuario && $usuario['rol'] === 'admin' && isset($_POST['nombre']) && !isset($_POST['edit_id'])) {
    $stmt = $pdo->prepare("INSERT INTO productos (nombre,descripcion,imagen,precio,stock) VALUES (?,?,?,?,?)");
    $stmt->execute([$_POST['nombre'], $_POST['descripcion'], $_POST['imagen'], $_POST['precio'], $_POST['stock']]);
    $msg = "🛒 Producto agregado.";
    $showAlert = true;
}

// --- ELIMINAR PRODUCTO ---
if ($usuario && $usuario['rol'] === 'admin' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id=?");
    $stmt->execute([$_POST['delete_id']]);
    $msg = "🗑️ Producto eliminado.";
    $showAlert = true;
}

// --- EDITAR PRODUCTO ---
if ($usuario && $usuario['rol'] === 'admin' && isset($_POST['edit_id'])) {
    $stmt = $pdo->prepare("UPDATE productos SET nombre=?, descripcion=?, imagen=?, precio=?, stock=? WHERE id=?");
    $stmt->execute([$_POST['nombre'], $_POST['descripcion'], $_POST['imagen'], $_POST['precio'], $_POST['stock'], $_POST['edit_id']]);
    $msg = "✏️ Producto actualizado.";
    $showAlert = true;
}

// --- COMPRAR PRODUCTO (solo usuario normal) ---
if ($usuario && $usuario['rol'] === 'usuario' && isset($_POST['producto_id'])) {
    $producto_id = $_POST['producto_id'];

    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id=?");
    $stmt->execute([$producto_id]);
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($prod && $prod['stock'] > 0) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id,total) VALUES (?,?)");
            $stmt->execute([$usuario['id'], $prod['precio']]);
            $pedido_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO pedido_items (pedido_id,producto_id,cantidad,precio_unitario) VALUES (?,?,?,?)");
            $stmt->execute([$pedido_id, $producto_id, 1, $prod['precio']]);

            $stmt = $pdo->prepare("UPDATE productos SET stock = stock - 1 WHERE id=?");
            $stmt->execute([$producto_id]);

            $pdo->commit();
            $msg = "🎉 Producto comprado correctamente 🛒";
            $showAlert = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "❌ Error al comprar: " . $e->getMessage();
            $showAlert = true;
        }
    } else {
        $msg = "⚠️ Producto sin stock";
        $showAlert = true;
    }
}

// --- LISTAR PRODUCTOS ---
$productos = $pdo->query("SELECT * FROM productos ORDER BY creado_en DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Tienda</title>
<style>
body{margin:0;font-family:sans-serif;background:#b87c4c;color:#fff;}
main{padding:20px;}
.producto{background:#fff;color:#000;padding:15px;border-radius:8px;margin:15px;display:inline-block;width:250px;vertical-align:top;}
.producto img{width:100%;border-radius:5px;margin-bottom:10px;}
button{padding:6px 10px;background:#5a3c1c;color:#fff;border:none;border-radius:4px;cursor:pointer;}
button:hover{background:#3d2914;}
button.delete{background:#c0392b;}
button.delete:hover{background:#96281b;}
button.no-stock{background:#c0392b;cursor:not-allowed;}
button.no-stock:hover{background:#96281b;}
.admin-actions{display:flex;justify-content:space-between;margin-top:10px;}
.admin-actions button{flex:1;margin:2px;}
input,textarea{width:100%;padding:8px;margin:5px 0;box-sizing:border-box;border:1px solid #ccc;border-radius:5px;resize:none;}
textarea{height:60px;}
.formulario{background:#fff;color:#000;padding:20px;border-radius:8px;width:400px;margin:20px auto;}
.alerta{position:fixed;bottom:20px;right:20px;background:#fff;color:#000;padding:15px;border-radius:8px;box-shadow:0px 4px 12px rgba(0,0,0,0.2);font-weight:bold;z-index:9999;animation:fadein 0.5s,fadeout 0.5s 3s;}
@keyframes fadein{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
@keyframes fadeout{from{opacity:1;transform:translateY(0);}to{opacity:0;transform:translateY(20px);}}
</style>
</head>
<body>
<?php include "header.php"; ?>
<main>
    <h2>Tienda 🛍️</h2>

    <!-- Formulario agregar producto (solo admin) -->
    <?php if($usuario && $usuario['rol']==='admin'): ?>
    <div class="formulario">
        <form method="post">
            <input type="text" name="nombre" placeholder="Nombre" required>
            <textarea name="descripcion" placeholder="Descripción"></textarea>
            <input type="text" name="imagen" placeholder="URL de la imagen">
            <input type="number" step="0.01" name="precio" placeholder="Precio" required>
            <input type="number" name="stock" placeholder="Stock" required>
            <button type="submit">Agregar</button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Lista de productos -->
    <?php foreach($productos as $p): ?>
    <div class="producto">
        <?php if($p['imagen']) echo "<img src='".htmlspecialchars($p['imagen'])."'>"; ?>
        <h3><?php echo htmlspecialchars($p['nombre']); ?></h3>
        <p><?php echo htmlspecialchars($p['descripcion']); ?></p>
        <p><b>Precio:</b> $<?php echo $p['precio']; ?></p>
        <p><b>Stock:</b> <?php echo $p['stock']; ?></p>

        <!-- Opciones usuario -->
        <?php if($usuario && $usuario['rol']==='usuario' && $p['stock']>0): ?>
            <form method="post">
                <input type="hidden" name="producto_id" value="<?php echo $p['id']; ?>">
                <button type="submit">Comprar</button>
            </form>
        <?php elseif(!$usuario): ?>
            <p><a href="login.php">Inicia sesión para comprar</a></p>
        <?php elseif($usuario['rol']==='admin'): ?>
            <p style="color:red;font-weight:bold;">(Admin no puede comprar)</p>
        <?php else: ?>
            <button class="no-stock" disabled>Sin stock</button>
        <?php endif; ?>

        <!-- Opciones admin -->
        <?php if($usuario && $usuario['rol']==='admin'): ?>
        <form method="post">
            <input type="hidden" name="edit_id" value="<?php echo $p['id']; ?>">
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($p['nombre']); ?>">
            <textarea name="descripcion"><?php echo htmlspecialchars($p['descripcion']); ?></textarea>
            <input type="text" name="imagen" value="<?php echo htmlspecialchars($p['imagen']); ?>">
            <input type="number" step="0.01" name="precio" value="<?php echo $p['precio']; ?>">
            <input type="number" name="stock" value="<?php echo $p['stock']; ?>">

            <div class="admin-actions">
                <button type="submit">Actualizar</button>
        </form>
        <form method="post">
            <input type="hidden" name="delete_id" value="<?php echo $p['id']; ?>">
            <button type="submit" class="delete">Eliminar</button>
        </form>
            </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</main>

<!-- Mini alerta -->
<?php if($showAlert): ?>
<div class="alerta" id="alerta"><?php echo $msg; ?></div>
<script>
setTimeout(function(){ var alerta=document.getElementById("alerta"); if(alerta) alerta.remove(); }, 4000);
</script>
<?php endif; ?>
</body>
</html>
