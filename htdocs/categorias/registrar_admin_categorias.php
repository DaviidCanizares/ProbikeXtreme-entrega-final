<?php
// Iniciar sesión
session_start();

// Verificamos si el usuario está autenticado como administrador
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['administrador', 'empleado'])) {
    header('Location: ../index.php');
    exit();
}

// Incluimos conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Obtenemos las categorías principales
try {
    $sql = "SELECT * FROM categories WHERE parent_id IS NULL";
    $stmt = $pdo->query($sql);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener las categorías: " . $e->getMessage());
}

// Inicializamos mensaje
$message = '';

// Procesamos el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $estado = $_POST['estado'] ?? 'activo';
    $parent_id = ($_POST['parent_id'] === 'new') ? null : $_POST['parent_id']; // Manejar "Nueva Categoría"

    if ($nombre) {
        try {
            // Insertamos la nueva categoría o subcategoría
            $sql = "INSERT INTO categories (nombre, estado, parent_id) VALUES (:nombre, :estado, :parent_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':estado' => $estado,
                ':parent_id' => $parent_id,
            ]);

            $message = "Categoría registrada con éxito.";
        } catch (PDOException $e) {
            $message = "Error al registrar la categoría: " . $e->getMessage();
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
    <title>Registrar Categoría</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>

<body>
    <?php include __DIR__ . '/../includes/header_admin.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Registrar Categoría</h2>

        <!-- Mensaje -->
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'éxito') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label for="parent_id" class="form-label">Categoría Principal</label>
                <select name="parent_id" id="parent_id" class="form-select" required>
                    <option value="new">Nueva Categoría</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo $categoria['id']; ?>">
                            <?php echo htmlspecialchars($categoria['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre de la Categoría o Subcategoría</label>
                <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Ingresa el nombre" required>
            </div>
            <div class="col-md-6">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" id="estado" class="form-select">
                    <option value="activo" selected>Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>
            <!-- Botones de acción -->
            <div class="col-md-12 d-flex justify-content-between align-items-center mt-4 mb-4">
                <a href="/categorias/categorias_admin_general.php" class="btn btn-secondary">Volver al Panel</a>
                <button type="submit" class="btn btn-second-color">Registrar Categoría</button>
            </div>
        </form>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-auto fixed-bottom">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>
</body>

</html>
