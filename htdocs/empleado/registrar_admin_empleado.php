<?php
// Iniciar sesión
session_start();

// Verificar si el usuario es administrador
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
    $direccion = $_POST['direccion'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $estado = $_POST['estado'] ?? 'activo';
    $role = $_POST['role'] ?? 'empleado';

    if ($nombre && $email && $password && $direccion && $telefono) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $sql = "INSERT INTO users (nombre, email, password, direccion, telefono, role, estado) 
                    VALUES (:nombre, :email, :password, :direccion, :telefono, :role, :estado)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':password' => $hashed_password,
                ':direccion' => $direccion,
                ':telefono' => $telefono,
                ':role' => $role,
                ':estado' => $estado,
            ]);

            $message = "Empleado registrado con éxito.";
        } catch (PDOException $e) {
            $message = "Error al registrar el empleado: " . $e->getMessage();
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
    <title>Registrar Empleado</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../includes/header_admin.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Registrar Empleado</h2>

        <!-- Mensaje -->
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'éxito') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de registro -->
        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre del Empleado</label>
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
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" name="direccion" id="direccion" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" name="telefono" id="telefono" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" id="estado" class="form-select">
                    <option value="activo" selected>Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="role" class="form-label">Rol</label>
                <select name="role" id="role" class="form-select">
                    <option value="empleado" selected>Empleado</option>
                    <option value="administrador">Administrador</option>
                </select>
            </div>

            <!-- Botones de acción -->
            <div class="col-md-12 d-flex justify-content-between align-items-center mt-4 mb-4">
                <a href="/empleado/empleado_admin_general.php" class="btn btn-secondary">Volver al Panel</a>
                <button type="submit" class="btn btn-second-color">Registrar Empleado</button>
            </div>
        </form>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
