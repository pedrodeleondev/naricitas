<?php include "conexion.php"; include "jwt.php"; 
$msg="";
if($_SERVER['REQUEST_METHOD']==='POST'){
    $usuario=$_POST['usuario']; $password=$_POST['password'];
    $stmt=$pdo->prepare("SELECT * FROM usuarios WHERE nombre=? OR email=?");
    $stmt->execute([$usuario,$usuario]); $user=$stmt->fetch(PDO::FETCH_ASSOC);
    if($user && password_verify($password,$user['password'])){
        $payload=["id"=>$user['id'],"rol"=>$user['rol'],"exp"=>time()+3600];
        $token=generarJWT($payload,$secret);
        setcookie("token",$token,time()+3600,"/");
        header("Location:index.php"); exit;
    } else { $msg="Credenciales inválidas"; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login</title>
<style>
body{margin:0;font-family:sans-serif;background:#b87c4c;}
.container{display:flex;justify-content:center;align-items:center;height:100vh;}
form{background:#fff;padding:30px;border-radius:8px;width:350px;text-align:left;}
h2{text-align:center;color:#3a2c20;}
label{display:block;margin-top:15px;font-weight:bold;color:#3a2c20;}
input{width:100%;padding:10px;margin-top:5px;border:1px solid #ccc;border-radius:4px;}
button{margin-top:20px;width:100%;padding:12px;background:#5a3c1c;color:white;border:none;border-radius:5px;font-size:16px;cursor:pointer;}
button:hover{background:#3d2914;}
p{text-align:center;}
</style>
</head>
<body>
<?php include "header.php"; ?>
<div class="container">
<form method="post">
    <h2>INICIAR SESIÓN</h2>
    <label>Usuario:</label>
    <input type="text" name="usuario" placeholder="Ingresa tu usuario" required>
    <label>Contraseña:</label>
    <input type="password" name="password" placeholder="Ingresa tu contraseña" required>
    <button type="submit">Iniciar Sesión</button>
    <p style="color:red;"><?php echo $msg; ?></p>
    <p>¿No tienes cuenta? <a href="registro.php"><b>Crea una aquí</b></a></p>
</form>
</div>
</body>
</html>
