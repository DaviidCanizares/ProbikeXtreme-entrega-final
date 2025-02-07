<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include __DIR__ . '/../includes/conectar_db.php';

// Mensaje para el usuario
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && isset($_POST['nombre']) && !isset($_POST['new_password'])) {
        // PRIMER FORMULARIO: Verificamos email y nombre de usuario
        $email = trim($_POST['email']);
        $nombre = trim($_POST['nombre']);

        try {
            $sql = "SELECT * FROM users WHERE email = :email AND nombre = :nombre";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':email' => $email, ':nombre' => $nombre]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Guardamos en sesión el usuario para el cambio de contraseña
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_nombre'] = $nombre;
            } else {
                $message = "No se encontró un usuario con esos datos.";
            }
        } catch (PDOException $e) {
            $message = "Error al verificar los datos: " . $e->getMessage();
        }
    } elseif (isset($_POST['new_password']) && isset($_SESSION['reset_email'])) {
        // SEGUNDO FORMULARIO: Cambiamos la contraseña
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $email = $_SESSION['reset_email'];
        $nombre = $_SESSION['reset_nombre'];

        try {
            $sql = "UPDATE users SET password = :password WHERE email = :email AND nombre = :nombre";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':password' => $new_password, ':email' => $email, ':nombre' => $nombre]);

            // Eliminamos la sesión y mostramos mensaje de éxito
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_nombre']);

            $message = "Tu contraseña ha sido actualizada con éxito.";
            $success = true;
        } catch (PDOException $e) {
            $message = "Error al actualizar la contraseña: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - ProBikeXtreme</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
    
    <?php if ($success): ?>
        <meta http-equiv="refresh" content="3;url=index.php">
    <?php endif; ?>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Header -->
    <header class="p-3 bg-dark text-white text-center">
        <?php include __DIR__ . '/../includes/header.php'; ?>
    </header>

    <!-- Contenido Principal -->
    <div class="container mt-5 flex-grow-1">
        <h2 class="text-center mb-4">Recuperar Contraseña</h2>

        <!-- Mostrar mensaje -->
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['reset_email'])): ?>
            <!-- PRIMER FORMULARIO: Verificamos email y nombre -->
            <form action="recuperar_contrasena.php" method="post" class="bg-light p-4 rounded shadow">
                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Ingresa tu correo" required>
                </div>
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre de usuario:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ingresa tu usuario" required>
                </div>
                <button type="submit" class="btn btn-formulario w-75 d-block mx-auto">Verificar Cuenta</button>
            </form>
        <?php else: ?>
            <!-- SEGUNDO FORMULARIO: Cambiamos la contraseña -->
            <form action="recuperar_contrasena.php" method="post" class="bg-light p-4 rounded shadow">
                <div class="mb-3">
                    <label for="new_password" class="form-label">Nueva Contraseña:</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Ingresa tu nueva contraseña" required>
                </div>
                <button type="submit" class="btn btn-formulario w-75 d-block mx-auto">Actualizar Contraseña</button>
            </form>
        <?php endif; ?>

        <p class="text-center mt-3">
            ¿Recordaste tu contraseña? <a href="index.php" class="text-decoration-none">Inicia sesión</a>
        </p>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center p-3 mt-auto">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>

</body>
</html>
