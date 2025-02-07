<?php
// Iniciar sesión
session_start();

// Verificamos si el usuario está autenticado como administrador o empleado
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['administrador', 'empleado'])) {
    header('Location: ../index.php');
    exit();
}

// Incluimos conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Inicializamos mensaje y variables de filtros
$message = '';
$filtro_nombre = $_GET['nombre'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';

// Configuramos paginación
$categorias_por_pagina = 5; 
$pagina_actual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
if ($pagina_actual < 1) {
    $pagina_actual = 1;
}
$offset = ($pagina_actual - 1) * $categorias_por_pagina;

// Armamos la consulta base con filtros
$sql_base = "FROM categories WHERE 1";
$parametros = [];

if (!empty($filtro_nombre)) {
    $sql_base .= " AND nombre LIKE :nombre";
    $parametros[':nombre'] = '%' . $filtro_nombre . '%';
}
if (!empty($filtro_estado)) {
    $sql_base .= " AND estado = :estado";
    $parametros[':estado'] = $filtro_estado;
}

// Primero, contamos el total de categorías que cumplen los filtros
try {
    $sql_total = "SELECT COUNT(*) AS total " . $sql_base;
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->execute($parametros);
    $total_categorias = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    die("Error al contar las categorías: " . $e->getMessage());
}

// Calculamos el total de páginas
$total_paginas = ceil($total_categorias / $categorias_por_pagina);

// Ahora armamos la consulta final para obtener las categorías con LIMIT y OFFSET
$sql_final = "SELECT * " . $sql_base . " ORDER BY id DESC LIMIT :offset, :limit";
$stmt = $pdo->prepare($sql_final);

// Vinculamos los parámetros de filtro
foreach ($parametros as $key => $value) {
    $stmt->bindValue($key, $value);
}
// Vinculamos los parámetros de paginación
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $categorias_por_pagina, PDO::PARAM_INT);

try {
    $stmt->execute();
    $categorias_result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error al buscar categorías: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Categorías</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/../includes/header_admin.php'; ?>

    <div class="container mt-5 flex-grow-1">
        <h2 class="text-center mb-4">Gestión de Categorías</h2>

        <!-- Formulario de búsqueda -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-6">
                <input type="text" name="nombre" class="form-control" placeholder="Nombre de la categoría" value="<?php echo htmlspecialchars($filtro_nombre); ?>">
            </div>
            <div class="col-md-4">
                <select name="estado" class="form-select">
                    <option value="">-- Estado --</option>
                    <option value="activo" <?php echo $filtro_estado === 'activo' ? 'selected' : ''; ?>>Activo</option>
                    <option value="inactivo" <?php echo $filtro_estado === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-first-color">Buscar</button>
                <a href="/categorias/registrar_admin_categorias.php" class="btn btn-second-color">Registrar Categoría</a>
            </div>
        </form>

        <!-- Tabla de categorías -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($categorias_result)): ?>
                    <?php foreach ($categorias_result as $categoria): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($categoria['id']); ?></td>
                            <td><?php echo htmlspecialchars($categoria['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($categoria['estado']); ?></td>
                            <td>
                                <a href="/categorias/editar_admin_categorias.php?id=<?php echo $categoria['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="/categorias/borrar_admin_categorias.php?id=<?php echo $categoria['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de dar de baja esta categoría?');">Baja</a>
                                <?php if ($categoria['estado'] === 'inactivo'): ?>
                                    <a href="/categorias/alta_admin_categorias.php?id=<?php echo $categoria['id']; ?>" class="btn btn-success btn-sm ms-2" onclick="return confirm('¿Estás seguro de dar de alta esta categoría?');">Alta</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No hay categorías registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <?php if ($total_categorias > $categorias_por_pagina): ?>
            <nav aria-label="Paginación de categorías">
                <ul class="pagination justify-content-center">
                    <?php if ($pagina_actual > 1): ?>
                        <li class="page-item">
                            <a class="page-link btn-second-color" href="?nombre=<?php echo urlencode($filtro_nombre); ?>&estado=<?php echo urlencode($filtro_estado); ?>&pagina=<?php echo $pagina_actual - 1; ?>" aria-label="Anterior">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?php echo ($pagina_actual === $i) ? 'active' : ''; ?>">
                            <a class="page-link btn-second-color" href="?nombre=<?php echo urlencode($filtro_nombre); ?>&estado=<?php echo urlencode($filtro_estado); ?>&pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagina_actual < $total_paginas): ?>
                        <li class="page-item">
                            <a class="page-link btn-second-color" href="?nombre=<?php echo urlencode($filtro_nombre); ?>&estado=<?php echo urlencode($filtro_estado); ?>&pagina=<?php echo $pagina_actual + 1; ?>" aria-label="Siguiente">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

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
