<?php
// Iniciar sesión
session_start();

// Verificamos si el usuario está autenticado como administrador
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrador') {
    header('Location: ../index.php');
    exit();
}

// Incluir conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

$message = '';
$id = $_GET['id'] ?? null;

// Verificamos si se proporcionó un ID válido
if (!$id || !is_numeric($id)) {
    header('Location: clientes_admin_general.php?message=ID inválido');
    exit();
}

// Obtenemos datos del cliente
try {
    $sql = "SELECT * FROM users WHERE id = :id AND role = 'cliente'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        header('Location: clientes_admin_general.php?message=Cliente no encontrado');
        exit();
    }
} catch (PDOException $e) {
    die("Error al obtener datos del cliente: " . $e->getMessage());
}

// Procesamos formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $estado = $_POST['estado'] ?? 'activo';
    $role = $_POST['role'] ?? 'cliente'; // Nuevo campo para el role

    if ($nombre && $email && $direccion && $telefono) {
        try {
            $sql = "UPDATE users 
                    SET nombre = :nombre, email = :email, direccion = :direccion, telefono = :telefono, estado = :estado, role = :role 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':direccion' => $direccion,
                ':telefono' => $telefono,
                ':estado' => $estado,
                ':role' => $role,  // Actualizamos el role
                ':id' => $id
            ]);

            $message = "Cliente actualizado con éxito.";
        } catch (PDOException $e) {
            $message = "Error al actualizar el cliente: " . $e->getMessage();
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
    <title>Editar Cliente</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../includes/header_admin.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Editar Cliente</h2>

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
                <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($cliente['email']); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="direccion" class="form-label">Dirección</label>
                <input type="text" name="direccion" id="direccion" class="form-control" value="<?php echo htmlspecialchars($cliente['direccion']); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" name="telefono" id="telefono" class="form-control" value="<?php echo htmlspecialchars($cliente['telefono']); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" id="estado" class="form-select">
                    <option value="activo" <?php echo $cliente['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo $cliente['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="role" class="form-label">Rol</label>
                <select name="role" id="role" class="form-select">
                    <option value="cliente" <?php echo $cliente['role'] === 'cliente' ? 'selected' : ''; ?>>Cliente</option>
                    <option value="empleado" <?php echo $cliente['role'] === 'empleado' ? 'selected' : ''; ?>>Empleado</option>
                    <option value="administrador" <?php echo $cliente['role'] === 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                </select>
            </div>
            <div class="col-md-12 d-flex justify-content-between align-items-center mt-4 mb-4">
                <a href="/cliente/clientes_admin_general.php" class="btn btn-secondary">Volver al Panel</a>
                <button type="submit" class="btn btn-second-color">Guardar cambios</button>
            </div>
        </form>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-auto"">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>
</body>
</html>
