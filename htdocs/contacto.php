<?php
session_start();
include __DIR__ . '/../includes/conectar_db.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - ProBikeXtreme</title>
    <?php include __DIR__ . '/includes/enlaces_bootstrap.php'; ?>
</head>

<body class="form-container content">

    <!-- Header -->
    <header class="bg-dark text-white p-1 fixed-top">
        <?php include __DIR__ . '/includes/header.php'; ?>
    </header>

    <!-- Contenido Principal -->
    <div class="container-fluid p-4 ">
        <h2 class="text-center mb-4 p-5 pb-5">Contáctanos</h2>

        <div class="row">
            <div class="col-md-6">
                <h3>Información de Contacto</h3>
                <p><strong>Dirección:</strong> Calle Doctor Marañón, Alicante, España</p>
                <p><strong>Teléfono:</strong> +34 123 456 789</p>
                <p><strong>Email:</strong> contacto@probikextreme.com</p>
                <p><strong>Horario de atención:</strong> Lunes a Viernes - 9:00 a 18:00</p>
            </div>
            <div class="col-md-6">
                <h3>Envíanos un Mensaje</h3>
                <form action="procesar_contacto.php" method="POST" class="bg-light p-4 rounded shadow">
                    <div class="text-black mb-3">
                        <label for="nombre" class="form-label">Nombre:</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" required>
                    </div>
                    <div class="text-black mb-3">
                        <label for="email" class="form-label">Correo Electrónico:</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="text-black mb-3">
                        <label for="mensaje" class="form-label">Mensaje:</label>
                        <textarea id="mensaje" name="mensaje" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-formulario w-100">Enviar Mensaje</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="fixed-bottom">
        <?php include __DIR__ . '/includes/footer.php'; ?>
    </footer>

</body>
</html>
