<?php
// Iniciar sesión y verificar que el usuario tenga un rol permitido (administrador o empleado)
session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['administrador', 'empleado'])) {
    header('Location: ../index.php');
    exit();
}

// Incluir la conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

$message = '';
$resultados = [];

// PAGINACIÓN
$devoluciones_por_pagina = 10; // Define cuántas devoluciones mostrar por página
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) {
    $pagina_actual = 1;
}
$offset = ($pagina_actual - 1) * $devoluciones_por_pagina;

// Primero, contamos el total de devoluciones
try {
    $sql_total = "SELECT COUNT(*) AS total FROM devoluciones";
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->execute();
    $total_devoluciones = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    die("Error al contar las devoluciones: " . $e->getMessage());
}

$total_paginas = ceil($total_devoluciones / $devoluciones_por_pagina);

// Consulta para obtener las devoluciones con paginación
try {
    $sql = "SELECT d.*, 
                   u.nombre AS nombre_usuario, 
                   p.id AS pedido_numero, 
                   pr.nombre AS nombre_producto
            FROM devoluciones d
            LEFT JOIN users u ON d.usuario_id = u.id
            LEFT JOIN pedidos p ON d.pedido_id = p.id
            LEFT JOIN products pr ON d.producto_id = pr.id
            ORDER BY d.fecha_solicitud DESC
            LIMIT :offset, :limit";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $devoluciones_por_pagina, PDO::PARAM_INT);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error al obtener las devoluciones: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Devoluciones</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Header -->
    <header class="bg-dark p-3">
        <?php include __DIR__ . '/../includes/header_admin.php'; ?>
    </header>

    <!-- Contenido principal -->
    <div class="container mt-5 flex-grow-1">
        <h2 class="text-center mb-4">Listado de Devoluciones</h2>

        <!-- Mensaje de error o confirmación -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-warning">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Tabla de Devoluciones -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>ID Pedido</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Fecha Solicitud</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($resultados)): ?>
                    <?php foreach ($resultados as $devolucion): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($devolucion['id']); ?></td>
                            <td><?php echo htmlspecialchars($devolucion['nombre_usuario'] ?? 'Sin asignar'); ?></td>
                            <td><?php echo htmlspecialchars($devolucion['pedido_id']); ?></td>
                            <td><?php echo htmlspecialchars($devolucion['nombre_producto'] ?? 'Sin asignar'); ?></td>
                            <td><?php echo htmlspecialchars($devolucion['cantidad']); ?></td>
                            <td><?php echo htmlspecialchars($devolucion['fecha_solicitud']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No se encontraron devoluciones.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <?php if ($total_paginas > 1): ?>
            <nav aria-label="Paginación de devoluciones">
                <ul class="pagination justify-content-center">
                    <?php if ($pagina_actual > 1): ?>
                        <li class="page-item">
                            <a class="page-link btn-second-color" href="?pagina=<?php echo $pagina_actual - 1; ?>" aria-label="Anterior">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?php echo ($pagina_actual === $i) ? 'active' : ''; ?>">
                            <a class="page-link btn-second-color" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($pagina_actual < $total_paginas): ?>
                        <li class="page-item">
                            <a class="page-link btn-second-color" href="?pagina=<?php echo $pagina_actual + 1; ?>" aria-label="Siguiente">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <!-- Botón de Volver -->
        <div class="text-end mt-4 mb-4">
            <?php if ($_SESSION['role'] === 'administrador'): ?>
                <a href="../administrador/index_administrador.php" class="btn btn-secondary">Volver al Panel de Administrador</a>
            <?php elseif ($_SESSION['role'] === 'empleado'): ?>
                <a href="../empleado/index_empleado.php" class="btn btn-secondary">Volver al Panel de Empleado</a>
            <?php endif; ?>
        </div>
    </div> <!-- Fin del container -->

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>
</body>
</html>
