<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "conexion.php";
include "jwt.php";

function eliminarPerrito($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM perritos WHERE id=?");
    $stmt->execute([$id]);
    return "🐶 Perrito eliminado.";
}

function editarPerrito($pdo, $data) {
    $stmt = $pdo->prepare("UPDATE perritos SET nombre=?, edad=?, sexo=?, estado_salud=?, historia=?, foto=? WHERE id=?");
    $stmt->execute([$data['nombre'], $data['edad'], $data['sexo'], $data['estado_salud'], $data['historia'], $data['foto'], $data['edit_id']]);
    return "🐶 Perrito actualizado.";
}

function toggleAdopcion($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE perritos SET adoptado = CASE WHEN adoptado=1 THEN 0 ELSE 1 END WHERE id=?");
    $stmt->execute([$id]);
    return "🔄 Estado de adopción actualizado.";
}

function adoptarPerrito($pdo, $id, $nombre, $usuarioId) {
    $stmt = $pdo->prepare("UPDATE perritos SET adoptado=1, adoptado_por=? WHERE id=? AND adoptado=0");
    $stmt->execute([$usuarioId, $id]);
    if ($stmt->rowCount() > 0) {
        return "🎉 Felicidades, has adoptado a $nombre, pasa por tu nuev@ amig@ a nuestra sucursal 🐾";
    }
    return "⚠️ Este perrito ya fue adoptado.";
}

function agregarPerrito($pdo, $data) {
    $stmt = $pdo->prepare("INSERT INTO perritos (nombre, edad, sexo, estado_salud, historia, foto, adoptado, creado_en) VALUES (?,?,?,?,?,?,0,NOW())");
    $stmt->execute([$data['nombre'], $data['edad'], $data['sexo'], $data['estado_salud'], $data['historia'], $data['foto']]);
    return "🐶 Nuevo perrito agregado.";
}

$usuario = null;
if (isset($_COOKIE['token'])) {
    $usuario = validarJWT($_COOKIE['token'], $secret);
}

$msg = "";
$showAlert = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $usuario) {
    if ($usuario['rol'] === 'admin' && isset($_POST['delete_id'])) {
        $msg = eliminarPerrito($pdo, $_POST['delete_id']);
        $showAlert = true;
    }
    if ($usuario['rol'] === 'admin' && isset($_POST['edit_id'])) {
        $msg = editarPerrito($pdo, $_POST);
        $showAlert = true;
    }
    if ($usuario['rol'] === 'admin' && isset($_POST['toggle_id'])) {
        $msg = toggleAdopcion($pdo, $_POST['toggle_id']);
        $showAlert = true;
    }
    if ($usuario['rol'] === 'usuario' && isset($_POST['perrito_id'])) {
        $msg = adoptarPerrito($pdo, $_POST['perrito_id'], $_POST['nombre'], $usuario['id']);
        $showAlert = true;
    }
    if ($usuario['rol'] === 'admin' && isset($_POST['new_perrito'])) {
        $msg = agregarPerrito($pdo, $_POST);
        $showAlert = true;
    }
}

$perritos = $pdo->query("SELECT * FROM perritos ORDER BY creado_en DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Perritos en adopción</title>
<style>
body{margin:0;font-family:sans-serif;background:#b87c4c;color:#fff;}
main{padding:20px;}
.perrito{background:#fff;color:#000;padding:15px;border-radius:8px;margin:15px;display:inline-block;width:250px;vertical-align:top;}
.perrito img{width:100%;border-radius:5px;}
button{padding:6px 10px;background:#5a3c1c;color:#fff;border:none;border-radius:4px;cursor:pointer;}
button:hover{background:#3d2914;}
form{margin-top:5px;}
.admin-actions{display:flex;justify-content:space-between;margin-top:10px;}
.admin-actions button{flex:1;margin:2px;}
button.delete{background:#c0392b;}
button.delete:hover{background:#96281b;}
.alerta{position:fixed;bottom:20px;right:20px;background:#fff;color:#000;padding:15px;border-radius:8px;box-shadow:0px 4px 12px rgba(0,0,0,0.2);font-weight:bold;z-index:9999;animation:fadein 0.5s,fadeout 0.5s 3s;}
@keyframes fadein{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
@keyframes fadeout{from{opacity:1;transform:translateY(0);}to{opacity:0;transform:translateY(20px);}}
.new-form{background:#fff;color:#000;padding:15px;border-radius:8px;margin:15px;}
.new-form h3{margin-top:0;}
.new-form input,.new-form select,.new-form textarea{display:block;width:100%;margin:5px 0;padding:5px;border:1px solid #ccc;border-radius:4px;}
</style>
</head>
<body>
<?php include "header.php"; ?>
<main>
<h2>Perritos en adopción 🐾</h2>

<?php foreach($perritos as $p): ?>
<div class="perrito">
    <?php if($p['foto']) echo "<img src='".htmlspecialchars($p['foto'])."'>"; ?>
    <h3><?php echo htmlspecialchars($p['nombre']); ?></h3>
    <p><b>Edad:</b> <?php echo $p['edad']; ?></p>
    <p><b>Sexo:</b> <?php echo $p['sexo']; ?></p>
    <p><b>Estado de salud:</b> <?php echo $p['estado_salud']; ?></p>
    <p><?php echo $p['historia']; ?></p>

    <?php if($p['adoptado']): ?>
        <p style="color:green;font-weight:bold;">✅ Adoptado</p>
        <?php if($usuario && $usuario['rol']==='admin' && $p['adoptado_por']): ?>
            <?php 
                $adoptante = $pdo->prepare("SELECT nombre FROM usuarios WHERE id=?");
                $adoptante->execute([$p['adoptado_por']]);
                $adoptante = $adoptante->fetch(PDO::FETCH_ASSOC);
                if($adoptante){
                    echo "<p><b>Adoptado por:</b> ".htmlspecialchars($adoptante['nombre'])."</p>";
                }
            ?>
        <?php endif; ?>
    <?php elseif($usuario && $usuario['rol']==='usuario'): ?>
        <form method="post">
            <input type="hidden" name="perrito_id" value="<?php echo $p['id']; ?>">
            <input type="hidden" name="nombre" value="<?php echo $p['nombre']; ?>">
            <button type="submit">Adoptar</button>
        </form>
    <?php endif; ?>

    <?php if($usuario && $usuario['rol']==='admin'): ?>
        <form method="post">
            <input type="hidden" name="toggle_id" value="<?php echo $p['id']; ?>">
            <button type="submit">Cambiar estado adopción</button>
        </form>

        <form method="post">
            <input type="hidden" name="edit_id" value="<?php echo $p['id']; ?>">
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($p['nombre']); ?>">
            <input type="text" name="edad" value="<?php echo htmlspecialchars($p['edad']); ?>">
            <select name="sexo">
                <option <?php if($p['sexo']=="Macho") echo "selected"; ?>>Macho</option>
                <option <?php if($p['sexo']=="Hembra") echo "selected"; ?>>Hembra</option>
            </select>
            <input type="text" name="estado_salud" value="<?php echo htmlspecialchars($p['estado_salud']); ?>">
            <textarea name="historia"><?php echo htmlspecialchars($p['historia']); ?></textarea>
            <input type="text" name="foto" value="<?php echo htmlspecialchars($p['foto']); ?>">
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

<?php if($usuario && $usuario['rol']==='admin'): ?>
<div class="new-form">
    <h3>Agregar nuevo perrito 🐶</h3>
    <form method="post">
        <input type="hidden" name="new_perrito" value="1">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="text" name="edad" placeholder="Edad" required>
        <select name="sexo">
            <option>Macho</option>
            <option>Hembra</option>
        </select>
        <input type="text" name="estado_salud" placeholder="Estado de salud" required>
        <textarea name="historia" placeholder="Historia"></textarea>
        <input type="text" name="foto" placeholder="URL de foto">
        <button type="submit">Agregar</button>
    </form>
</div>
<?php endif; ?>

</main>

<?php if($showAlert): ?>
<div class="alerta" id="alerta"><?php echo $msg; ?></div>
<script>
setTimeout(function(){ var alerta=document.getElementById("alerta"); if(alerta) alerta.remove(); }, 4000);
</script>
<?php endif; ?>
</body>
</html>
