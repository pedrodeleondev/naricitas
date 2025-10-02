<?php include "conexion.php"; include "jwt.php"; 
$usuario=null;
if(isset($_COOKIE['token'])) $usuario=validarJWT($_COOKIE['token'],$secret);
if(!$usuario){ header("Location: login.php"); exit; }

$stmt=$pdo->prepare("SELECT nombre,email,rol FROM usuarios WHERE id=?");
$stmt->execute([$usuario['id']]);
$datos=$stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Perfil</title>
<style>
body{margin:0;font-family:sans-serif;background:#b87c4c;color:#fff;}
main{padding:20px;text-align:center;}
.card{background:#fff;color:#000;padding:20px;border-radius:8px;max-width:400px;margin:20px auto;}
</style>
</head>
<body>
<?php include "header.php"; ?>
<main>
<h2>Mi perfil 👤</h2>
<div class="card">
    <p><b>Nombre:</b> <?php echo $datos['nombre']; ?></p>
    <p><b>Email:</b> <?php echo $datos['email']; ?></p>
    <p><b>Rol:</b> <?php echo $datos['rol']; ?></p>
</div>
</main>
</body>
</html>
