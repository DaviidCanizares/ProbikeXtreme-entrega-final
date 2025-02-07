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

// Inicializamos mensaje y variables de filtros
$message = '';
$filtro_nombre = $_GET['nombre'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';

// Configuramos paginación
$empleados_por_pagina = 5;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) {
    $pagina_actual = 1;
}
$offset = ($pagina_actual - 1) * $empleados_por_pagina;

// Armamos la consulta base con filtros para seleccionar empleados y administradores
$sql_base = "FROM users WHERE role IN ('empleado', 'administrador')";

$parametros = [];
if (!empty($filtro_nombre)) {
    $sql_base .= " AND nombre LIKE :nombre";
    $parametros[':nombre'] = '%' . $filtro_nombre . '%';
}
if (!empty($filtro_estado)) {
    $sql_base .= " AND estado = :estado";
    $parametros[':estado'] = $filtro_estado;
}

// Primero, contamos el total de empleados que cumplen los filtros
try {
    $sql_total = "SELECT COUNT(*) AS total " . $sql_base;
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->execute($parametros);
    $total_empleados = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    die("Error al contar los empleados: " . $e->getMessage());
}

// Calculamos el total de páginas
$total_paginas = ceil($total_empleados / $empleados_por_pagina);

// Ahora armamos la consulta final para obtener los empleados con LIMIT y OFFSET
$sql_final = "SELECT * " . $sql_base . " ORDER BY id DESC LIMIT :offset, :limit";
$stmt = $pdo->prepare($sql_final);

// Vinculamos parámetros de filtro
foreach ($parametros as $key => $value) {
    $stmt->bindValue($key, $value);
}
// Vinculamos parámetros de paginación
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $empleados_por_pagina, PDO::PARAM_INT);

try {
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error al buscar empleados: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Empleados y Administradores</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>
<body>
    <header>
        <?php include __DIR__ . '/../includes/header_admin.php'; ?>
    </header>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Gestión de Empleados y Administradores</h2>

        <!-- Formulario de Búsqueda -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-6">
                <input type="text" name="nombre" class="form-control" placeholder="Nombre del empleado/administrador" value="<?php echo htmlspecialchars($filtro_nombre); ?>">
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
                <a href="/empleado/registrar_admin_empleado.php" class="btn btn-second-color">Registrar Empleado</a>
            </div>
        </form>

        <!-- Tabla de Empleados y Administradores -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Estado</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($resultados)): ?>
                    <?php foreach ($resultados as $empleado): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($empleado['id']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['email']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['estado']); ?></td>
                            <td><?php echo htmlspecialchars($empleado['role']); ?></td>
                            <td>
                                <a href="/empleado/editar_admin_empleado.php?id=<?php echo $empleado['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <?php if ($empleado['estado'] === 'activo'): ?>
                                    <a href="/empleado/borrar_admin_empleado.php?id=<?php echo $empleado['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de dar de baja este empleado?');">Baja</a>
                                <?php endif; ?>
                                <?php if ($empleado['estado'] === 'inactivo'): ?>
                                    <a href="/empleado/alta_admin_empleado.php?id=<?php echo $empleado['id']; ?>" class="btn btn-success btn-sm">Alta</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No se encontraron empleados/administradores.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <?php if ($total_empleados > $empleados_por_pagina): ?>
            <nav aria-label="Paginación de empleados">
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

        <!-- Botón de Volver -->
        <div class="text-end mt-4 mb-4">
            <a href="../administrador/index_administrador.php" class="btn btn-secondary">Volver al Panel de Administrador</a>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>
</body>
</html>
