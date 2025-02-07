<?php
// Iniciar sesión
session_start();

// Verificamos si el usuario es administrador
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrador') {
    header('Location: ../index.php');
    exit();
}

// Incluimos conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Inicializamos mensaje
$message = '';

// Procesamos el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $estado = $_POST['estado'] ?? 'activo';

    if ($nombre && $email && $password && $telefono && $direccion) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO users (nombre, email, password, telefono, direccion, role, estado) 
                    VALUES (:nombre, :email, :password, :telefono, :direccion, 'cliente', :estado)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':password' => $hashed_password,
                ':telefono' => $telefono,
                ':direccion' => $direccion,
                ':estado' => $estado,
            ]);

            $message = "Cliente registrado con éxito.";
        } catch (PDOException $e) {
            $message = "Error al registrar el cliente: " . $e->getMessage();
        }
    } else {
        $message = "Por favor, completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Cliente</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../includes/header_admin.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Registrar Cliente</h2>

        <!-- Mensaje -->
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'éxito') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de registro -->
        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre del Cliente</label>
                <input type="text" name="nombre" id="nombre" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" name="telefono" id="telefono" class="form-control" required>
            </div>
            <div class="col-md-12">
                <label for="direccion" class="form-label">Dirección</label>
                <textarea name="direccion" id="direccion" class="form-control" rows="3" required></textarea>
            </div>
            <div class="col-md-6">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" id="estado" class="form-select">
                    <option value="activo" selected>Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>


            <div class="col-md-12 d-flex justify-content-between align-items-center mt-4 mb-4">
                <a href="/cliente/clientes_admin_general.php" class="btn btn-secondary">Volver al Panel</a>
                <button type="submit" class="btn btn-second-color">Registrar Cliente</button>
            </div>
        </form>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
