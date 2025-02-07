<?php
// Iniciar sesión
session_start();

// Verificamos si el usuario es administrador o empleado
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['administrador', 'empleado'])) {
    header('Location: ../index.php');
    exit();
}

// Incluimos conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Inicializamos mensaje
$message = '';

// Obtenemos categorías y subcategorías
try {
    $sql_categorias = "SELECT * FROM categories";
    $categorias = $pdo->query($sql_categorias)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener categorías: " . $e->getMessage());
}

// Procesamos el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = $_POST['precio'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $categoria_id = $_POST['categoria_id'] ?? '';
    $estado = $_POST['estado'] ?? 'activo';
    $imagen = $_FILES['imagen'] ?? null;

    // Validamos campos obligatorios
    if ($nombre && $descripcion && $precio && $stock && $categoria_id && $imagen) {
        // Subimos imagen
        $ruta_imagen = null;
        if ($imagen['error'] === UPLOAD_ERR_OK) {
            $nombre_imagen = basename($imagen['name']);
            $ruta_destino = __DIR__ . '/../assets/imagenes/' . $nombre_imagen;

            if (move_uploaded_file($imagen['tmp_name'], $ruta_destino)) {
                $ruta_imagen = '/assets/imagenes/' . $nombre_imagen;
            } else {
                $message = "Error al subir la imagen.";
            }
        }

        // Validamos si la categoría seleccionada es válida
        try {
            $sql_validar_categoria = "SELECT COUNT(*) FROM categories WHERE id = :categoria_id";
            $stmt_validar = $pdo->prepare($sql_validar_categoria);
            $stmt_validar->execute([':categoria_id' => $categoria_id]);
            $es_valida = $stmt_validar->fetchColumn();

            if (!$es_valida) {
                $message = "La categoría o subcategoría seleccionada no es válida.";
            } else {
                // Continuamos con la inserción del producto
                if ($ruta_imagen) {
                    $sql = "INSERT INTO products (nombre, descripcion, precio, stock, categoria_id, estado, imagen) 
                            VALUES (:nombre, :descripcion, :precio, :stock, :categoria_id, :estado, :imagen)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':nombre' => $nombre,
                        ':descripcion' => $descripcion,
                        ':precio' => $precio,
                        ':stock' => $stock,
                        ':categoria_id' => $categoria_id,
                        ':estado' => $estado,
                        ':imagen' => $ruta_imagen,
                    ]);
                    $message = "Producto registrado con éxito.";
                }
            }
        } catch (PDOException $e) {
            $message = "Error al registrar el producto: " . $e->getMessage();
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
    <title>Registrar Producto</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>
<body>
    <header>
        <?php include __DIR__ . '/../includes/header_admin.php'; ?>
    </header>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Registrar Producto</h2>

        <!-- Mensaje -->
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'éxito') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de registro -->
        <form method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-6">
                <label for="nombre" class="form-label">Nombre del Producto</label>
                <input type="text" name="nombre" id="nombre" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="precio" class="form-label">Precio (€)</label>
                <input type="number" name="precio" id="precio" class="form-control" step="0.01" required>
            </div>
            <div class="col-md-6">
                <label for="stock" class="form-label">Stock</label>
                <input type="number" name="stock" id="stock" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="categoria_id" class="form-label">Categoría</label>
                <select name="categoria_id" id="categoria_id" class="form-select" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <?php if ($categoria['parent_id'] === NULL): // Categoría principal ?>
                            <optgroup label="<?php echo htmlspecialchars($categoria['nombre']); ?>">
                                <?php foreach ($categorias as $subcategoria): ?>
                                    <?php if ($subcategoria['parent_id'] === $categoria['id']): ?>
                                        <option value="<?php echo $subcategoria['id']; ?>">
                                            <?php echo htmlspecialchars($subcategoria['nombre']); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-12">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea name="descripcion" id="descripcion" class="form-control" rows="4" required></textarea>
            </div>
            <div class="col-md-12">
                <label for="imagen" class="form-label">Imagen</label>
                <input type="file" name="imagen" id="imagen" class="form-control" required>
            </div>
            <div class="col-md-12">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" id="estado" class="form-select">
                    <option value="activo" selected>Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>

            <!-- Botones de acción -->
            <div class="col-md-12 d-flex justify-content-between align-items-center mt-4 mb-4">
                <a href="/products/productos_admin_general.php" class="btn btn-secondary">Volver al Panel</a>
                <button type="submit" class="btn btn-second-color">Registrar Producto</button>
            </div>
        </form>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
