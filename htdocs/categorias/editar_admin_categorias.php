<?php
// Iniciar sesión
session_start();

// Verificar si el usuario está autenticado como administrador
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['administrador', 'empleado'])) {
    header('Location: ../index.php');
    exit();
}

// Incluir conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Verificamos si se proporciona un ID válido
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    header('Location: categorias_admin_general.php?message=ID inválido');
    exit();
}

// Inicializamos mensaje
$message = '';

// Procesamos el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $estado = $_POST['estado'] ?? 'activo';
    $parent_id = $_POST['parent_id'] ?? null;

    if ($nombre) {
        try {
            // Actualizamos la categoría en la base de datos
            $sql = "UPDATE categories SET nombre = :nombre, estado = :estado, parent_id = :parent_id WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':estado' => $estado,
                ':parent_id' => $parent_id ? $parent_id : null,
                ':id' => $id,
            ]);

            $message = "Categoría actualizada con éxito.";
        } catch (PDOException $e) {
            $message = "Error al actualizar la categoría: " . $e->getMessage();
        }
    } else {
        $message = "Por favor, completa todos los campos.";
    }
}

// Obtenemos los datos de la categoría actual
try {
    $sql = "SELECT * FROM categories WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$categoria) {
        header('Location: categorias_admin_general.php?message=Categoría no encontrada');
        exit();
    }
} catch (PDOException $e) {
    die("Error al obtener la categoría: " . $e->getMessage());
}

// Obtenemos todas las categorías para el select dinámico
try {
    $sql_categorias = "SELECT * FROM categories WHERE id != :id";
    $stmt = $pdo->prepare($sql_categorias);
    $stmt->execute([':id' => $id]);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener las categorías: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoría</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>

<body>
    <?php include __DIR__ . '/../includes/header_admin.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Editar Categoría</h2>

        <!-- Mensaje -->
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'éxito') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre de la Categoría</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo htmlspecialchars($categoria['nombre']); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="parent_id" class="form-label">Categoría Principal</label>
                <select name="parent_id" id="parent_id" class="form-select">
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                            <?php echo ($categoria['parent_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" id="estado" class="form-select">
                    <option value="activo" <?php echo $categoria['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo $categoria['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
            <div class="col-md-12 d-flex justify-content-between align-items-center mt-4 mb-4">
                <a href="/categorias/categorias_admin_general.php" class="btn btn-secondary">Volver al Panel</a>
                <button type="submit" class="btn btn-second-color">Guardar cambios</button>
            </div>
        </form>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-auto fixed-bottom">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>
</body>

</html>
