<?php
include "conexion.php";
include "jwt.php";

$msg = "";
$success = false;
$redirect = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['usuario']);
    $email  = trim($_POST['email']);
    $passwordHash = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $rol = ($_POST['rol'] == "admin") ? "admin" : "usuario";

    if (preg_match('/\s/', $nombre)) {
        $msg = "⚠️ El nombre de usuario no puede contener espacios.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre,email,password,rol) VALUES (?,?,?,?)");
            $stmt->execute([$nombre, $email, $passwordHash, $rol]);

            // Obtener ID del nuevo usuario
            $userId = $pdo->lastInsertId();

            // Crear JWT para iniciar sesión automáticamente
            $payload = ["id"=>$userId, "rol"=>$rol, "exp"=>time()+3600];
            $token = generarJWT($payload, $secret);
            setcookie("token", $token, time()+3600, "/");

            $msg = "✅ Usuario creado correctamente. Serás redirigido al inicio en <span id='countdown'>3</span> segundos...";
            $success = true;
            $redirect = true;

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                if (strpos($e->getMessage(), 'email') !== false) {
                    $msg = "⚠️ Este correo ya está registrado.";
                } else {
                    $msg = "⚠️ Este usuario ya existe. Intenta con otro nombre.";
                }
            } else {
                $msg = "❌ Error inesperado: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <style>
        body{margin:0;font-family:sans-serif;background:#b87c4c;}
        .container{display:flex;justify-content:center;align-items:center;height:100vh;}
        form{background:#fff;padding:30px;border-radius:8px;width:350px;text-align:left;}
        h2{text-align:center;color:#3a2c20;}
        label{display:block;margin-top:15px;font-weight:bold;color:#3a2c20;}
        input,select{width:100%;padding:10px;margin-top:5px;border:1px solid #ccc;border-radius:4px;}
        button{margin-top:20px;width:100%;padding:12px;background:#5a3c1c;color:white;border:none;border-radius:5px;font-size:16px;cursor:pointer;}
        button:hover{background:#3d2914;}
        p{text-align:center;}
        .msg{margin-top:10px;text-align:center;font-weight:bold;}
        .msg.error{color:red;}
        .msg.ok{color:green;}
    </style>
</head>
<body>
<?php include "header.php"; ?>
<div class="container">
    <form method="post">
        <h2>REGISTRO</h2>

        <label>Usuario (sin espacios):</label>
        <input type="text" name="usuario" placeholder="ej. FridaGarza" 
               required pattern="^\S+$" 
               title="El usuario no puede contener espacios">

        <label>Correo electrónico:</label>
        <input type="email" name="email" placeholder="ej. tu_nombre@fake.com" required>

        <label>Contraseña:</label>
        <input type="password" name="password" placeholder="ej. Ao9sdfs*" required>
        
        <label>Tipo de usuario:</label>
        <select name="rol">
            <option value="usuario">General</option>
            <option value="admin">Administrador</option>
        </select>
        
        <button type="submit">Registrarse</button>
        
        <?php if($msg): ?>
            <p class="msg <?php echo $success ? 'ok' : 'error'; ?>">
                <?php echo $msg; ?>
            </p>
        <?php endif; ?>
        
        <p>¿Ya tienes cuenta? <a href="login.php"><b>Inicia sesión</b></a></p>
    </form>
</div>

<?php if($redirect): ?>
<script>
let seconds = 3;
let countdown = document.getElementById("countdown");
let interval = setInterval(function(){
    seconds--;
    if(countdown) countdown.textContent = seconds;
    if(seconds <= 0){
        clearInterval(interval);
        window.location.href = "index.php";
    }
},1000);
</script>
<?php endif; ?>
</body>
</html>
