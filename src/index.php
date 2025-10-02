<?php include "conexion.php"; include "jwt.php"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Inicio - Naricitas</title>
<style>
body {margin:0;font-family:sans-serif;background:#b87c4c;color:#fff;}
main {padding:30px;text-align:center;}
section {margin:40px auto;max-width:900px;}
h2 {color:#fff;font-size:28px;margin-bottom:20px;text-transform:uppercase;}
p {font-size:16px;line-height:1.5;}

.historia {background:#fff;color:#000;padding:15px;border-radius:8px;margin:15px;display:inline-block;width:250px;vertical-align:top;text-align:left;}
.historia img {width:100%;border-radius:5px;}
.historia h3 {margin:10px 0;color:#5a3c1c;}

.sucursal {background:#fff;color:#000;padding:20px;border-radius:8px;margin-top:30px;text-align:left;}
.sucursal h3 {color:#5a3c1c;margin-bottom:10px;}
</style>
</head>
<body>
<?php include "header.php"; ?>

<main>
    <!-- Sección Quienes somos -->
    <section>
        <h2>¿Quiénes somos?</h2>
        <p>
            En <b>Naricitas</b> trabajamos todos los días para darles una segunda oportunidad
            a los perritos y gatitos en situación de calle. Nuestro objetivo es encontrarles
            un hogar lleno de amor y cuidado. 🐶🐱
        </p>
    </section>

    <!-- Sección Historias felices -->
    <section>
        <h2>Historias felices</h2>
        <div class="historia">
            <img src="https://placedog.net/400/280?id=10" alt="Nala">
            <h3>Nala</h3>
            <p>Nala fue rescatada en 2022 y hoy disfruta de un hogar lleno de juegos y cariño.</p>
        </div>
        <div class="historia">
            <img src="https://placedog.net/400/280?id=15" alt="Mila">
            <h3>Mila</h3>
            <p>Mila fue adoptada gracias a Naricitas y ahora acompaña a su nueva familia en todas sus aventuras.</p>
        </div>
        <div class="historia">
            <img src="https://placedog.net/400/280?id=20" alt="Rocky">
            <h3>Rocky</h3>
            <p>Rocky era un perrito callejero y hoy es el consentido de la casa.</p>
        </div>
    </section>

    <!-- Sección Sucursal -->
    <section>
        <h2>Nuestra Sucursal</h2>
        <div class="sucursal">
            <h3>Sucursal Guadalupe, Nuevo León</h3>
            <p>📍 Av. Solidaridad #1234, Col. Centro, Guadalupe, N.L.</p>
            <p>📞 Teléfono: (81) 1234-5678</p>
            <p>🕐 Horario: Lunes a Viernes de 9:00 a 18:00 hrs</p>
        </div>
    </section>
</main>

</body>
</html>
