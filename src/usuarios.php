<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "conexion.php";
include "jwt.php";
$usuario = null;
if (isset($_COOKIE['token'])) {
    $usuario = validarJWT($_COOKIE['token'], $secret);
}
if(!$usuario || $usuario['rol']!=='admin'){
    header("Location: index.php");
    exit;
}
$msg = "";
$showAlert = false;
if(isset($_POST['delete_id'])){
    $id = $_POST['delete_id'];
    if($id != $usuario['id']){
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id=?");
        $stmt->execute([$id]);
        $msg = "🗑️ Usuario eliminado correctamente.";
        $showAlert = true;
    } else {
        $msg = "❌ No puedes eliminarte a ti mismo.";
        $showAlert = true;
    }
}
$usuarios = $pdo->query("SELECT id,nombre,email,rol,creado_en FROM usuarios ORDER BY creado_en DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Administrar usuarios</title>
<style>
body{margin:0;font-family:sans-serif;background:#b87c4c;color:#fff;}
main{padding:20px;}
table{width:90%;margin:20px auto;border-collapse:collapse;background:#fff;color:#000;}
th,td{padding:10px;border:1px solid #ccc;text-align:center;word-wrap:break-word;}
td.email{max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
button{padding:6px 10px;background:#c0392b;color:#fff;border:none;border-radius:4px;cursor:pointer;}
button:hover{background:#96281b;}
.alerta{position:fixed;bottom:20px;right:20px;background:#fff;color:#000;padding:15px;border-radius:8px;box-shadow:0px 4px 12px rgba(0,0,0,0.2);font-weight:bold;z-index:9999;animation:fadein 0.5s,fadeout 0.5s 3s;}
@keyframes fadein{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
@keyframes fadeout{from{opacity:1;transform:translateY(0);}to{opacity:0;transform:translateY(20px);}}
</style>
<script>
function confirmarEliminar(form){
    let confirmacion = prompt("⚠️ Para confirmar escribe: ELIMINAR");
    if(confirmacion === "ELIMINAR"){
        return true;
    } else {
        alert("Operación cancelada. Debes escribir ELIMINAR exactamente.");
        return false;
    }
}
</script>
</head>
<body>
<?php include "header.php"; ?>
<main>
    <h2>Administración de Usuarios 👤</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Fecha de registro</th>
            <th>Acciones</th>
        </tr>
        <?php foreach($usuarios as $u): ?>
        <tr>
            <td><?php echo $u['id']; ?></td>
            <td><?php echo htmlspecialchars($u['nombre']); ?></td>
            <td class="email" title="<?php echo htmlspecialchars($u['email']); ?>"><?php echo htmlspecialchars($u['email']); ?></td>
            <td><?php echo $u['rol']; ?></td>
            <td><?php echo $u['creado_en']; ?></td>
            <td>
                <?php if($u['id'] != $usuario['id']): ?>
                <form method="post" style="display:inline;" onsubmit="return confirmarEliminar(this);">
                    <input type="hidden" name="delete_id" value="<?php echo $u['id']; ?>">
                    <button type="submit">Eliminar</button>
                </form>
                <?php else: ?>
                <span style="color:gray;">(Tú mismo)</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</main>
<?php if($showAlert): ?>
<div class="alerta" id="alerta"><?php echo $msg; ?></div>
<script>
setTimeout(function(){ var alerta=document.getElementById("alerta"); if(alerta) alerta.remove(); }, 4000);
</script>
<?php endif; ?>
</body>
</html>
