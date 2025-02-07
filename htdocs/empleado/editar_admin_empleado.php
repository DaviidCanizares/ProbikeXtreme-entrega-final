<?php
// Iniciar sesión
session_start();

// Verificamos si el usuario es administrador
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrador') {
    header('Location: ../index.php');
    exit();
}

// Incluir conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

$message = '';
$id = $_GET['id'] ?? null;

// Verificamos si el ID es válido
if (!$id || !is_numeric($id)) {
    header('Location: clientes_admin_general.php?message=ID inválido');
    exit();
}

// Obtenemos datos del empleado
try {
    // Si queremos permitir cambiar el role, quitamos la condición AND role = 'empleado'
    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $empleado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$empleado) {
        die("Empleado no encontrado.");
    }
} catch (PDOException $e) {
    die("Error al obtener datos del empleado: " . $e->getMessage());
}

// Procesamos formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $estado = $_POST['estado'] ?? 'activo';
    $role = $_POST['role'] ?? 'empleado'; // Nuevo campo para el role

    if ($nombre && $email && $telefono && $direccion) {
        try {
            // Actualizamos también el campo role
            $sql = "UPDATE users 
                    SET nombre = :nombre, email = :email, telefono = :telefono, direccion = :direccion, estado = :estado, role = :role
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre'    => $nombre,
                ':email'     => $email,
                ':telefono'  => $telefono,
                ':direccion' => $direccion,
                ':estado'    => $estado,
                ':role'      => $role,
                ':id'        => $id,
            ]);

            $message = "Empleado actualizado con éxito.";
            // Actualizamos los datos del empleado para mostrarlos en la página
            $empleado['nombre'] = $nombre;
            $empleado['email'] = $email;
            $empleado['telefono'] = $telefono;
            $empleado['direccion'] = $direccion;
            $empleado['estado'] = $estado;
            $empleado['role'] = $role;
        } catch (PDOException $e) {
            $message = "Error al actualizar el empleado: " . $e->getMessage();
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
    <title>Editar Empleado</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../includes/header_admin.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Editar Empleado</h2>

        <!-- Mensaje -->
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'éxito') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de edición -->
        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($empleado['nombre']); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($empleado['email']); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" name="telefono" id="telefono" class="form-control" value="<?php echo htmlspecialchars($empleado['telefono'] ?? ''); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" name="direccion" id="direccion" class="form-control" value="<?php echo htmlspecialchars($empleado['direccion'] ?? ''); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" id="estado" class="form-select">
                    <option value="activo" <?php echo $empleado['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo $empleado['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
            <!-- Nuevo contenedor para cambiar el role -->
            <div class="col-md-6">
                <label for="role" class="form-label">Rol</label>
                <select name="role" id="role" class="form-select">
                    <option value="cliente" <?php echo $empleado['role'] === 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                    <option value="empleado" <?php echo $empleado['role'] === 'empleado' ? 'selected' : ''; ?>>Empleado</option>
                    <option value="administrador" <?php echo $empleado['role'] === 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                </select>
            </div>
            <div class="col-md-12 d-flex justify-content-between align-items-center mt-4 mb-4">
                <a href="/empleado/empleado_admin_general.php" class="btn btn-secondary">Volver al Panel</a>
                <button type="submit" class="btn btn-second-color">Guardar cambios</button>  
            </div>
        </form>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>
</body>
</html>
