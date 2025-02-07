<?php
// Iniciamos sesión
session_start();

// Verificamos si el usuario está autenticado como administrador o empleado
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['administrador', 'empleado'])) {
    header('Location: ../index.php');
    exit();
}

// Incluimos conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Inicializamos mensaje y datos del producto
$message = '';
$producto = null;
$id = $_GET['id'] ?? null;

// Verificamos si se recibió un ID válido
if ($id) {
    try {
        // Obtenemos el producto desde la base de datos
        $sql = "SELECT * FROM products WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            $message = "Producto no encontrado.";
        }
    } catch (PDOException $e) {
        $message = "Error al obtener el producto: " . $e->getMessage();
    }
} else {
    $message = "No se proporcionó un ID de producto válido.";
}

// Obtenemos las categorías para el desplegable
try {
    $sql_categorias = "SELECT id, nombre FROM categories ORDER BY nombre ASC";
    $stmt_categorias = $pdo->prepare($sql_categorias);
    $stmt_categorias->execute();
    $categories = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener las categorías: " . $e->getMessage());
}

// Definimos los tipos de producto disponibles (si no disponemos de una tabla en la BBDD)
$tipos_producto = ["bicicleta", "nutricion", "accesorio", "equipamiento"];

// Actualizamos el producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $producto) {
    // Obtenemos datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = $_POST['precio'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $categoria_id = $_POST['categoria_id'] ?? '';
    $tipo_producto = $_POST['tipo_producto'] ?? '';
    $transmision = $_POST['transmision'] ?? '';
    $peso = $_POST['peso'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $imagen = $_POST['imagen'] ?? '';
    $marca_id = $_POST['marca_id'] ?? '';

    try {
        // Actualizamos el producto en la base de datos
        $sql = "UPDATE products SET
                    nombre = :nombre,
                    descripcion = :descripcion,
                    precio = :precio,
                    stock = :stock,
                    categoria_id = :categoria_id,
                    tipo_producto = :tipo_producto,
                    transmision = :transmision,
                    peso = :peso,
                    estado = :estado,
                    imagen = :imagen,
                    marca_id = :marca_id
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':precio' => $precio,
            ':stock' => $stock,
            ':categoria_id' => $categoria_id,
            ':tipo_producto' => $tipo_producto,
            ':transmision' => $transmision,
            ':peso' => $peso,
            ':estado' => $estado,
            ':imagen' => $imagen,
            ':marca_id' => $marca_id,
            ':id' => $id,
        ]);

        $message = "Producto actualizado correctamente.";
        // Actualizamos los datos del producto en la página
        $producto = array_merge($producto, $_POST);
    } catch (PDOException $e) {
        $message = "Error al actualizar el producto: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>
<body>
    <header>
        <?php include __DIR__ . '/../includes/header_admin.php'; ?>
    </header>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Editar Producto</h2>

        <!-- Mensaje -->
        <?php if (!empty($message)): ?>
            <div class="alert <?php echo strpos($message, 'correctamente') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($producto): ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" rows="4" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="precio" class="form-label">Precio (€)</label>
                    <input type="number" id="precio" name="precio" class="form-control" step="0.01" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="stock" class="form-label">Stock</label>
                    <input type="number" id="stock" name="stock" class="form-control" value="<?php echo htmlspecialchars($producto['stock']); ?>" required>
                </div>
                <!-- Desplegable de Categoría -->
                <div class="mb-3">
                    <label for="categoria_id" class="form-label">Categoría</label>
                    <select id="categoria_id" name="categoria_id" class="form-select" required>
                        <option value="">Seleccione una categoría</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php if($producto['categoria_id'] == $cat['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Desplegable de Tipo de Producto -->
                <div class="mb-3">
                    <label for="tipo_producto" class="form-label">Tipo de Producto</label>
                    <select id="tipo_producto" name="tipo_producto" class="form-select" required>
                        <option value="">Seleccione un tipo de producto</option>
                        <?php foreach ($tipos_producto as $tipo): ?>
                            <option value="<?php echo htmlspecialchars($tipo); ?>" <?php if($producto['tipo_producto'] == $tipo) echo 'selected'; ?>>
                                <?php echo ucfirst($tipo); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="transmision" class="form-label">Transmisión</label>
                    <input type="text" id="transmision" name="transmision" class="form-control" value="<?php echo htmlspecialchars($producto['transmision']); ?>">
                </div>
                <div class="mb-3">
                    <label for="peso" class="form-label">Peso (kg)</label>
                    <input type="number" id="peso" name="peso" class="form-control" step="0.01" value="<?php echo htmlspecialchars($producto['peso']); ?>">
                </div>
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select id="estado" name="estado" class="form-select" required>
                        <option value="activo" <?php echo $producto['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $producto['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="imagen" class="form-label">Imagen (URL)</label>
                    <input type="text" id="imagen" name="imagen" class="form-control" value="<?php echo htmlspecialchars($producto['imagen']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="marca_id" class="form-label">Marca ID</label>
                    <input type="number" id="marca_id" name="marca_id" class="form-control" value="<?php echo htmlspecialchars($producto['marca_id']); ?>">
                </div>
                
                 <!-- Botones de acción -->
                <div class="col-md-12 d-flex justify-content-between align-items-center mt-4 mb-4">
                    <a href="/products/productos_admin_general.php" class="btn btn-secondary">Volver al Panel</a>
                    <button type="submit" class="btn btn-second-color">Guardar cambios</button>  
                </div>
            </form>
        <?php else: ?>
            <p class="text-danger">No se encontró el producto.</p>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
