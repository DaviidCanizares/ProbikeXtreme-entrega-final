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

// Inicializamos mensaje y variables de filtros
$message = '';
$filtro_nombre = $_GET['nombre'] ?? '';
$filtro_email = $_GET['email'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';

// CONFIGURAR PAGINACIÓN
$clientes_por_pagina = 5; // Número de clientes por página (ajusta según necesidad)
$pagina_actual = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
if ($pagina_actual < 1) {
    $pagina_actual = 1;
}
$offset = ($pagina_actual - 1) * $clientes_por_pagina;

// Armamos la consulta base con filtros para los clientes
$sql_base = "FROM users WHERE role = 'cliente'";
$parametros = [];
if (!empty($filtro_nombre)) {
    $sql_base .= " AND nombre LIKE :nombre";
    $parametros[':nombre'] = '%' . $filtro_nombre . '%';
}
if (!empty($filtro_email)) {
    $sql_base .= " AND email LIKE :email";
    $parametros[':email'] = '%' . $filtro_email . '%';
}
if (!empty($filtro_estado)) {
    $sql_base .= " AND estado = :estado";
    $parametros[':estado'] = $filtro_estado;
}

// Primero, contamos el total de clientes que cumplen los filtros
try {
    $sql_total = "SELECT COUNT(*) AS total " . $sql_base;
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->execute($parametros);
    $total_clientes = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    die("Error al contar los clientes: " . $e->getMessage());
}

// Calculamos el total de páginas
$total_paginas = ceil($total_clientes / $clientes_por_pagina);

// Ahora armamos la consulta final para obtener los clientes con LIMIT y OFFSET
$sql_final = "SELECT * " . $sql_base . " ORDER BY id DESC LIMIT :offset, :limit";
$stmt = $pdo->prepare($sql_final);

// Vinculamos los parámetros de filtro
foreach ($parametros as $key => $value) {
    $stmt->bindValue($key, $value);
}
// Vinculamos los parámetros de paginación
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $clientes_por_pagina, PDO::PARAM_INT);

try {
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error al buscar clientes: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Clientes</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>
<body>
    <?php include __DIR__ . '/../includes/header_admin.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Gestión de Clientes</h2>

        <!-- Mensaje -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-warning"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Formulario de Búsqueda -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="text" name="nombre" class="form-control" placeholder="Nombre del cliente" value="<?php echo htmlspecialchars($filtro_nombre); ?>">
            </div>
            <div class="col-md-4">
                <input type="email" name="email" class="form-control" placeholder="Email del cliente" value="<?php echo htmlspecialchars($filtro_email); ?>">
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
                <a href="registrar_admin_clientes.php" class="btn btn-second-color">Registrar Cliente</a>
            </div>
        </form>

        <!-- Tabla de Clientes -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($resultados)): ?>
                    <?php foreach ($resultados as $cliente): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cliente['id']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['estado']); ?></td>
                            <td>
                                <a href="/cliente/editar_admin_clientes.php?id=<?php echo $cliente['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="/cliente/borrar_admin_clientes.php?id=<?php echo $cliente['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de desactivar este cliente?');">Baja</a>
                                <?php if ($cliente['estado'] === 'inactivo'): ?>
                                    <a href="/cliente/alta_admin_clientes.php?id=<?php echo $cliente['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('¿Estás seguro de activar este cliente?');">Alta</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No se encontraron clientes.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <?php if ($total_clientes > $clientes_por_pagina): ?>
            <nav aria-label="Paginación de clientes">
                <ul class="pagination justify-content-center">
                    <?php if ($pagina_actual > 1): ?>
                        <li class="page-item">
                            <a class="page-link btn-second-color" href="?nombre=<?php echo urlencode($filtro_nombre); ?>&email=<?php echo urlencode($filtro_email); ?>&estado=<?php echo urlencode($filtro_estado); ?>&pagina=<?php echo $pagina_actual - 1; ?>" aria-label="Anterior">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?php echo ($pagina_actual === $i) ? 'active' : ''; ?>">
                            <a class="page-link btn-second-color" href="?nombre=<?php echo urlencode($filtro_nombre); ?>&email=<?php echo urlencode($filtro_email); ?>&estado=<?php echo urlencode($filtro_estado); ?>&pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($pagina_actual < $total_paginas): ?>
                        <li class="page-item">
                            <a class="page-link btn-second-color" href="?nombre=<?php echo urlencode($filtro_nombre); ?>&email=<?php echo urlencode($filtro_email); ?>&estado=<?php echo urlencode($filtro_estado); ?>&pagina=<?php echo $pagina_actual + 1; ?>" aria-label="Siguiente">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <!-- Botón de Volver -->
        <div class="text-end mt-4 mb-4">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'administrador'): ?>
                <a href="../administrador/index_administrador.php" class="btn btn-secondary" style="font-weight: bold; font-size: 14px;">Volver al Panel de Administrador</a>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'empleado'): ?>
                <a href="../empleado/index_empleado.php" class="btn btn-secondary" style="font-weight: bold; font-size: 14px;">Volver al Panel de Empleado</a>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
