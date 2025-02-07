<?php
session_start();
include __DIR__ . '/../includes/conectar_db.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nosotros - ProBikeXtreme</title>
    <?php include __DIR__ . '/includes/enlaces_bootstrap.php'; ?>

</head>

<body class="text-white">

    <!-- Header Fijo -->
    <header class="bg-dark p-3">
        <?php include __DIR__ . '/includes/header.php'; ?>
    </header>

    <!-- Contenido Principal -->
    <main class="content container-fluid p-4">
        <h2 class="text-center mb-4 p-5">Sobre Nosotros</h2>

        <div class="row ">
            <div class="col-md-6">
                <img src="../assets/imagenes/sobreNosotros.jpg" alt="Nuestra historia" class="img-fluid rounded shadow">
            </div>
            <div class="col-md-6">
                <h3>Nuestra Historia</h3>
                <p>
                    Bienvenido a <strong>ProBikeXtreme</strong>, la tienda especializada en ciclismo donde la pasión por la aventura sobre ruedas nos impulsa a ofrecerte los mejores productos. 
                    Desde nuestra fundación en 2010, hemos trabajado con marcas líderes para brindarte bicicletas, accesorios y equipamiento de la más alta calidad.
                </p>
                <h3>Nuestra Misión</h3>
                <p>
                    Nos enfocamos en proporcionar un servicio excepcional a ciclistas de todos los niveles, desde principiantes hasta profesionales. Queremos ser tu compañero ideal en cada ruta, ayudándote a encontrar el mejor equipo según tus necesidades.
                </p>
            </div>
        </div>

        <div class="row mt-5 pb-5">
            <div class="col-md-4 text-center">
                <i class="bi bi-globe2 fs-1 text-primary pb-5"></i>
                <h4>Envíos a Todo el Mundo</h4>
                <p>Trabajamos con envíos rápidos y seguros para que recibas tus productos sin importar dónde estés.</p>
            </div>
            <div class="col-md-4 text-center">
                <i class="bi bi-tools fs-1 text-warning"></i>
                <h4>Soporte Técnico</h4>
                <p>Contamos con un equipo de expertos que pueden ayudarte con cualquier problema o duda sobre tu equipo.</p>
            </div>
            <div class="col-md-4 text-center pb-5">
                <i class="bi bi-bicycle fs-1 text-danger"></i>
                <h4>Pasión por el Ciclismo</h4>
                <p>Somos ciclistas apasionados que queremos compartir nuestra experiencia contigo.</p>
            </div>
        </div>
    </main>

    <!-- Footer Normal (no fijo) -->
    <footer class="footerUno">
        <?php include __DIR__ . '/includes/footer.php'; ?>
    </footer>

</body>
</html>