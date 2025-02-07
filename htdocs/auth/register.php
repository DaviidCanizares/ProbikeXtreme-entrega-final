<?php
// Incluir el archivo de conexión y validaciones
include __DIR__ . '/../includes/conectar_db.php';
include __DIR__ . '/../includes/validaciones.php';

// Inicializar mensaje de error o éxito
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiar entradas
    $nombre = limpiarEntrada($_POST['nombre']);
    $email = limpiarEntrada($_POST['email']);
    $password = limpiarEntrada($_POST['password']);
    $telefono = limpiarEntrada($_POST['telefono']);
    $direccion = limpiarEntrada($_POST['direccion']);

    $errores = [];

    // Validar nombre
    $validacionNombre = validarTexto($nombre);
    if (!$validacionNombre['valido']) {
        $errores[] = $validacionNombre['mensaje'];
    }

    // Validar contraseña
    $validacionPassword = validarPassword($password);
    if (!$validacionPassword['valido']) {
        $errores[] = $validacionPassword['mensaje'];
    }

    // Validar email
    $validacionEmail = validarEmail($email);
    if (!$validacionEmail['valido']) {
        $errores[] = $validacionEmail['mensaje'];
    }

    // Validar si el email ya existe
    $sql_check_email = "SELECT COUNT(*) FROM users WHERE email = :email";
    $stmt_check_email = $pdo->prepare($sql_check_email);
    $stmt_check_email->execute([':email' => $email]);
    if ($stmt_check_email->fetchColumn() > 0) {
        $errores[] = "El correo electrónico ya está registrado.";
    }

    // Si no hay errores, insertar datos
    if (empty($errores)) {
        try {
            // Hashear la contraseña
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insertar datos en la tabla 'users'
            $sql = "INSERT INTO users (nombre, email, password, telefono, direccion, role) VALUES (:nombre, :email, :password, :telefono, :direccion, 'cliente')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':password' => $hashed_password,
                ':telefono' => $telefono,
                ':direccion' => $direccion,
            ]);

            $message = "Registro exitoso. Ahora puedes iniciar sesión.";
        } catch (PDOException $e) {
            $message = "Error al registrar el cliente: " . $e->getMessage();
        }
    } else {
        // Concatenar errores para mostrarlos
        $message = implode('<br>', $errores);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Cliente - ProBikeXtreme</title>
     <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>

<body>
    <!-- Header -->
    <header class="p-3 bg-dark text-center">
         <?php include __DIR__ . '/../includes/header.php'; ?>
    </header>

    <!-- Registro -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">Registrar Cliente</h2>

        <!-- Mostrar mensaje -->
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'exitoso') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post" class="form-container-register">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre:</label>
                <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Introduce tu nombre" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Introduce tu email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña:</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Introduce tu contraseña" minlength="8" required>
            </div>

            <button type="submit" class="btn btn-formulario w-100">Registrar</button>
        </form>

        <p class="text-center mt-3">
            ¿Ya tienes una cuenta? <a href="../index.php" class="text-decoration-none">Inicia sesión</a>
        </p>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer >

</body>

</html>
