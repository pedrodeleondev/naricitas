<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "conexion.php";
include "jwt.php";

// ---------------- FUNCIONES ----------------
function agregarCampaña($pdo, $data) {
    $stmt = $pdo->prepare("INSERT INTO campañas (titulo,descripcion,fecha_inicio,fecha_fin) VALUES (?,?,?,?)");
    $stmt->execute([$data['titulo'], $data['descripcion'], $data['fecha_inicio'], $data['fecha_fin']]);
    return "📢 Campaña agregada.";
}

function eliminarCampaña($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM campañas WHERE id=?");
    $stmt->execute([$id]);
    return "🗑️ Campaña eliminada.";
}

function editarCampaña($pdo, $data) {
    $stmt = $pdo->prepare("UPDATE campañas SET titulo=?, descripcion=?, fecha_inicio=?, fecha_fin=? WHERE id=?");
    $stmt->execute([$data['titulo'], $data['descripcion'], $data['fecha_inicio'], $data['fecha_fin'], $data['edit_id']]);
    return "✏️ Campaña actualizada.";
}

// ---------------- MAIN ----------------
$usuario = null;
if (isset($_COOKIE['token'])) {
    $usuario = validarJWT($_COOKIE['token'], $secret);
}

$msg = "";
$showAlert = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $usuario) {
    if ($usuario['rol'] === 'admin' && isset($_POST['titulo']) && !isset($_POST['edit_id'])) {
        $msg = agregarCampaña($pdo, $_POST);
        $showAlert = true;
    }
    if ($usuario['rol'] === 'admin' && isset($_POST['delete_id'])) {
        $msg = eliminarCampaña($pdo, $_POST['delete_id']);
        $showAlert = true;
    }
    if ($usuario['rol'] === 'admin' && isset($_POST['edit_id'])) {
        $msg = editarCampaña($pdo, $_POST);
        $showAlert = true;
    }
}

$campañas = $pdo->query("SELECT * FROM campañas ORDER BY fecha_inicio DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Campañas</title>
<style>
body{margin:0;font-family:sans-serif;background:#b87c4c;color:#fff;}
main{padding:20px;}
.campaña{background:#fff;color:#000;padding:15px;border-radius:8px;margin:15px auto;width:500px;}
.campaña h3{margin-top:0;}
input,textarea{width:100%;padding:10px;margin:5px 0;box-sizing:border-box;border:1px solid #ccc;border-radius:5px;}
textarea{resize:none;height:80px;}
button{padding:8px 12px;background:#5a3c1c;color:#fff;border:none;border-radius:5px;cursor:pointer;}
button:hover{background:#3d2914;}
.admin-actions{display:flex;justify-content:space-between;margin-top:10px;}
.admin-actions button{flex:1;margin:2px;}
button.delete{background:#c0392b;}
button.delete:hover{background:#96281b;}
.alerta{position:fixed;bottom:20px;right:20px;background:#fff;color:#000;padding:15px;border-radius:8px;box-shadow:0px 4px 12px rgba(0,0,0,0.2);font-weight:bold;z-index:9999;animation:fadein 0.5s,fadeout 0.5s 3s;}
@keyframes fadein{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
@keyframes fadeout{from{opacity:1;transform:translateY(0);}to{opacity:0;transform:translateY(20px);}}
.formulario{background:#fff;color:#000;padding:20px;border-radius:8px;width:500px;margin:20px auto;}
</style>
</head>
<body>
<?php include "header.php"; ?>
<main>
    <h2>Campañas 📢</h2>

    <?php if($usuario && $usuario['rol']==='admin'): ?>
    <div class="formulario">
        <form method="post">
            <input type="text" name="titulo" placeholder="Título" required>
            <textarea name="descripcion" placeholder="Descripción"></textarea>
            <input type="date" name="fecha_inicio" required>
            <input type="date" name="fecha_fin" required>
            <button type="submit">Agregar</button>
        </form>
    </div>
    <?php endif; ?>

    <?php foreach($campañas as $c): ?>
    <div class="campaña">
        <h3><?php echo htmlspecialchars($c['titulo']); ?></h3>
        <p><?php echo htmlspecialchars($c['descripcion']); ?></p>
        <p><b>Inicio:</b> <?php echo $c['fecha_inicio']; ?> | <b>Fin:</b> <?php echo $c['fecha_fin']; ?></p>

        <?php if($usuario && $usuario['rol']==='admin'): ?>
        <form method="post">
            <input type="hidden" name="edit_id" value="<?php echo $c['id']; ?>">
            <input type="text" name="titulo" value="<?php echo htmlspecialchars($c['titulo']); ?>">
            <textarea name="descripcion"><?php echo htmlspecialchars($c['descripcion']); ?></textarea>
            <input type="date" name="fecha_inicio" value="<?php echo $c['fecha_inicio']; ?>">
            <input type="date" name="fecha_fin" value="<?php echo $c['fecha_fin']; ?>">
            <div class="admin-actions">
                <button type="submit">Actualizar</button>
        </form>
        <form method="post">
            <input type="hidden" name="delete_id" value="<?php echo $c['id']; ?>">
            <button type="submit" class="delete">Eliminar</button>
        </form>
            </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</main>

<?php if($showAlert): ?>
<div class="alerta" id="alerta"><?php echo $msg; ?></div>
<script>
setTimeout(function(){ var alerta=document.getElementById("alerta"); if(alerta) alerta.remove(); }, 4000);
</script>
<?php endif; ?>
</body>
</html>
