<?php
// Iniciar sesión
session_start();

// Verificamos si el usuario está autenticado como cliente
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'cliente') {
    echo "Acceso no autorizado. Por favor, inicia sesión como cliente.";
    exit();
}

// Incluimos el archivo de conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Inicializamos variables para almacenar los datos del cliente
$nombre = $email = $telefono = $direccion = '';
$message = ''; // Mensaje de éxito o error

// Obtenemos los datos del cliente desde la tabla users
try {
    $sql = "SELECT nombre, email, telefono, direccion FROM users WHERE id = :id AND role = 'cliente'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $_SESSION['id']]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        $nombre = $cliente['nombre'];
        $email = $cliente['email'];
        $telefono = $cliente['telefono'] ?? '';
        $direccion = $cliente['direccion'] ?? '';
    } else {
        echo "Usuario no encontrado.";
        exit();
    }
} catch (PDOException $e) {
    die("Error al obtener los datos del usuario: " . $e->getMessage());
}

// Procesamos la actualización de datos si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars($_POST['nombre']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? htmlspecialchars($_POST['email']) : '';
    $telefono = preg_match('/^\d{7,15}$/', $_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : '';
    $direccion = htmlspecialchars($_POST['direccion']);

    if ($email && $telefono) {
        try {
            $sql = "UPDATE users SET nombre = :nombre, email = :email, telefono = :telefono, direccion = :direccion WHERE id = :id AND role = 'cliente'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':telefono' => $telefono,
                ':direccion' => $direccion,
                ':id' => $_SESSION['id']
            ]);

            $message = "Datos actualizados correctamente.";
        } catch (PDOException $e) {
            $message = "Error al actualizar los datos: " . $e->getMessage();
        }
    } else {
        $message = "Por favor, verifica los datos ingresados.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Datos - Cliente</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>

<body>
    <header>
        <?php include __DIR__ . '/../includes/header.php'; ?>
    </header>

    <div class="container mt-5">
        <h2>Mis Datos</h2>

        <!-- Mostramos mensaje de éxito o error -->
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'correctamente') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="mis_datos.php" method="post">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo htmlspecialchars($nombre); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" id="telefono" name="telefono" class="form-control" value="<?php echo htmlspecialchars($telefono); ?>">
            </div>
            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" id="direccion" name="direccion" class="form-control" value="<?php echo htmlspecialchars($direccion); ?>">
            </div>
            <div>
                <a href="../cliente/index_cliente.php" class="btn btn-first-color p-1 mb-3">Volver a inicio</a>
                <button type="submit" class="btn btn-second-color p-1 mb-3">Actualizar Datos</button>
            </div>
        </form>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>

</html>
