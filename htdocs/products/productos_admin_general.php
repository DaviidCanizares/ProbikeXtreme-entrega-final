<?php
// Iniciar sesión
session_start();

// Verificamos si el usuario está autenticado como administrador o empleado
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['administrador', 'empleado'])) {
    header('Location: ../index.php');
    exit();
}

// Incluir conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Inicializamos mensaje y variables de filtros
$message = '';
$filtro_nombre = $_GET['nombre'] ?? '';
$filtro_categoria = $_GET['categoria'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';

// Configuramos paginación
$productos_por_pagina = 5; // Número de productos por página (ajusta según necesidad)
$pagina_actual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
if ($pagina_actual < 1) {
    $pagina_actual = 1;
}
$offset = ($pagina_actual - 1) * $productos_por_pagina;

// Obtener las categorías para el filtro
try {
    $sql_categorias = "SELECT * FROM categories ORDER BY nombre ASC";
    $stmt_categorias = $pdo->prepare($sql_categorias);
    $stmt_categorias->execute();
    $categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener categorías: " . $e->getMessage());
}

// Armamos la consulta base con filtros y agregamos el LEFT JOIN de promociones
$sql_base = "FROM products p 
             LEFT JOIN categories c ON p.categoria_id = c.id 
             LEFT JOIN promociones promo ON p.id = promo.producto_id 
             WHERE 1";

$parametros = [];
if (!empty($filtro_nombre)) {
    $sql_base .= " AND p.nombre LIKE :nombre";
    $parametros[':nombre'] = '%' . $filtro_nombre . '%';
}
if (!empty($filtro_categoria)) {
    $sql_base .= " AND (p.categoria_id = :categoria_id OR p.categoria_id IN (SELECT id FROM categories WHERE parent_id = :categoria_id))";
    $parametros[':categoria_id'] = $filtro_categoria;
}
if (!empty($filtro_estado)) {
    $sql_base .= " AND p.estado = :estado";
    $parametros[':estado'] = $filtro_estado;
}

// Primero, contamos el total de productos que cumplen los filtros
try {
    $sql_total = "SELECT COUNT(*) AS total " . $sql_base;
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->execute($parametros);
    $total_productos = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    die("Error al contar los productos: " . $e->getMessage());
}

// Calculamos el total de páginas
$total_paginas = ceil($total_productos / $productos_por_pagina);

// Ahora armamos la consulta final para obtener los productos con LIMIT y OFFSET
$sql_final = "SELECT p.*, 
                     c.nombre AS categoria_nombre, 
                     promo.descuento, 
                     (p.precio - (p.precio * IFNULL(promo.descuento, 0) / 100)) AS precio_con_descuento " 
             . $sql_base . " ORDER BY p.id DESC LIMIT :offset, :limit";
$stmt = $pdo->prepare($sql_final);

// Vinculamos parámetros de filtro
foreach ($parametros as $key => $value) {
    $stmt->bindValue($key, $value);
}
// Vinculamos parámetros de paginación
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $productos_por_pagina, PDO::PARAM_INT);

try {
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error al buscar productos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Productos</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- Header -->
    <header class="bg-dark p-3">
        <?php include __DIR__ . '/../includes/header_admin.php'; ?>
    </header>

    <!-- Contenido principal -->
    <div class="container mt-5 flex-grow-1">
        <h2 class="text-center mb-4">Gestión de Productos</h2>

        <!-- Mensaje de error o éxito -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-warning">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de Búsqueda -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="text" name="nombre" class="form-control" placeholder="Nombre del producto"
                       value="<?php echo htmlspecialchars($filtro_nombre); ?>">
            </div>
            <div class="col-md-4">
                <select name="categoria" class="form-select">
                    <option value="">-- Categoría --</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo htmlspecialchars($categoria['id']); ?>"
                            <?php echo (isset($filtro_categoria) && $filtro_categoria == $categoria['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($categoria['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select name="estado" class="form-select">
                    <option value="">-- Estado --</option>
                    <option value="activo" <?php echo ($filtro_estado === 'activo') ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo ($filtro_estado === 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-first-color">Buscar</button>
                <a href="/products/registrar_admin_producto.php" class="btn btn-second-color">Registrar Producto</a>
            </div>
        </form>

        <!-- Tabla de Productos -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Stock</th>
                    <th>Precio</th>
                    <th>Promoción</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($resultados)): ?>
                    <?php foreach ($resultados as $producto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($producto['id']); ?></td>
                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($producto['categoria_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($producto['stock']); ?></td>
                            <td><?php echo number_format($producto['precio'], 2); ?> €</td>
                            <td>
                                <?php if (!empty($producto['descuento']) && $producto['descuento'] > 0): ?>
                                    <strong><?php echo htmlspecialchars($producto['descuento']); ?>%</strong><br>
                                    <small>Precio Promo: <?php echo number_format($producto['precio_con_descuento'], 2); ?> €</small>
                                <?php else: ?>
                                    Sin promoción
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($producto['estado']); ?></td>
                            <td>
                                <a href="/products/editar_admin_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="/products/borrar_admin_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de dar de baja este producto?');">Baja</a>
                                <?php if ($producto['estado'] === 'inactivo'): ?>
                                    <a href="/products/alta_admin_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('¿Estás seguro de dar de alta este producto?');">Alta</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No se encontraron productos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <?php if ($total_productos > $productos_por_pagina): ?>
            <nav aria-label="Paginación de productos">
                <ul class="pagination justify-content-center">
                    <?php if ($pagina_actual > 1): ?>
                        <li class="page-item">
                            <a class="page-link btn-second-color" href="?nombre=<?php echo urlencode($filtro_nombre); ?>&categoria=<?php echo urlencode($filtro_categoria); ?>&estado=<?php echo urlencode($filtro_estado); ?>&pagina=<?php echo $pagina_actual - 1; ?>" aria-label="Anterior">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?php echo ($pagina_actual === $i) ? 'active' : ''; ?>">
                            <a class="page-link btn-second-color" href="?nombre=<?php echo urlencode($filtro_nombre); ?>&categoria=<?php echo urlencode($filtro_categoria); ?>&estado=<?php echo urlencode($filtro_estado); ?>&pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagina_actual < $total_paginas): ?>
                        <li class="page-item">
                            <a class="page-link btn-second-color" href="?nombre=<?php echo urlencode($filtro_nombre); ?>&categoria=<?php echo urlencode($filtro_categoria); ?>&estado=<?php echo urlencode($filtro_estado); ?>&pagina=<?php echo $pagina_actual + 1; ?>" aria-label="Siguiente">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>

    </div> <!-- Cierre del container -->

    <!-- Botón de Volver -->
    <div class="text-end mt-4 mb-4">
        <?php if ($_SESSION['role'] === 'administrador'): ?>
            <a href="../administrador/index_administrador.php" class="btn btn-secondary" style="font-weight: bold; font-size: 14px;">Volver al Panel de Administrador</a>
        <?php elseif ($_SESSION['role'] === 'empleado'): ?>
            <a href="../empleado/index_empleado.php" class="btn btn-secondary" style="font-weight: bold; font-size: 14px;">Volver al Panel de Empleado</a>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>
</body>
</html>
