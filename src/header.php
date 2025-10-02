<?php
include_once "conexion.php";
include_once "jwt.php";

$usuario = null;
if (isset($_COOKIE['token'])) {
    $usuario = validarJWT($_COOKIE['token'], $secret);
}
?>
<header>
    <div class="logo">🐾 Naricitas</div>
    <nav>
        <a href="index.php">INICIO</a>
        <a href="adoptar.php">ADOPTAR</a>
        <a href="campañas.php">CAMPAÑAS</a>
        <a href="tienda.php">TIENDA</a>
        <?php if($usuario && $usuario['rol']==='admin'): ?>
            <!-- SOLO ADMIN -->
            <a href="usuarios.php">USUARIOS</a>
        <?php endif; ?>
    </nav>
    <div class="icons">
        <a href="perfil.php">👤</a>
        <a href="pedidos.php">🛒</a>
        <?php if($usuario): ?>
            <!-- BOTÓN DE CERRAR SESIÓN -->
            <a href="logout.php" style="margin-left:15px;padding:6px 12px;background:#5a3c1c;color:#fff;
               border-radius:5px;text-decoration:none;font-weight:bold;">Salir</a>
        <?php endif; ?>
    </div>
</header>

<style>
header {
    background:#e5cfb0;
    padding:15px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.logo {
    font-size:20px;
    font-weight:bold;
    color:#3a2c20;
}
nav a {
    margin:0 15px;
    text-decoration:none;
    color:#3a2c20;
    font-weight:bold;
}
.icons a {
    margin-left:10px;
    font-size:16px;
}
</style>
