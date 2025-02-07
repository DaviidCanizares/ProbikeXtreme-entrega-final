<?php
// Iniciar sesión
session_start();

// Verificamos si el usuario está autenticado como administrador
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrador') {
    header('Location: ../index.php'); // Redirigir si no es administrador
    exit();
}

// Incluimos conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Variables para mensajes y resultados
$message = '';

try {
    //Datos sobre altas/bajas de usuarios (bajas lógicas)
    $sql_usuarios = "SELECT estado, COUNT(*) AS total FROM users WHERE estado IN ('activo', 'inactivo') GROUP BY estado";
    $stmt_usuarios = $pdo->prepare($sql_usuarios);
    $stmt_usuarios->execute();
    $usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);

    //Datos sobre altas/bajas de artículos (bajas lógicas)
    $sql_articulos = "SELECT estado, COUNT(*) AS total FROM products WHERE estado IN ('activo', 'inactivo') GROUP BY estado";
    $stmt_articulos = $pdo->prepare($sql_articulos);
    $stmt_articulos->execute();
    $articulos = $stmt_articulos->fetchAll(PDO::FETCH_ASSOC);

    //Estadísticas de pedidos
    $sql_pedidos = "SELECT COUNT(*) AS total_pedidos, COALESCE(SUM(total), 0) AS ingresos_totales 
                    FROM pedidos 
                    WHERE estado = 'procesado'";
    $stmt_pedidos = $pdo->prepare($sql_pedidos);
    $stmt_pedidos->execute();
    $pedidos = $stmt_pedidos->fetch(PDO::FETCH_ASSOC);

    //Productos más vendidos
    $sql_productos_vendidos = "SELECT p.nombre, SUM(dp.cantidad) AS total_vendido 
                               FROM detalles_pedido dp 
                               JOIN products p ON dp.producto_id = p.id 
                               WHERE p.estado = 'activo'
                               GROUP BY p.id 
                               ORDER BY total_vendido DESC 
                               LIMIT 5";
    $stmt_productos_vendidos = $pdo->prepare($sql_productos_vendidos);
    $stmt_productos_vendidos->execute();
    $productos_vendidos = $stmt_productos_vendidos->fetchAll(PDO::FETCH_ASSOC);

    //Ventas del mes (Ingresos, etc.)
    $sql_ventas_mes = "SELECT COALESCE(SUM(total), 0) AS ingresos_mes 
                       FROM pedidos 
                       WHERE MONTH(fecha_pedido) = MONTH(CURRENT_DATE()) 
                       AND YEAR(fecha_pedido) = YEAR(CURRENT_DATE())
                       AND estado = 'procesado'";
    $stmt_ventas_mes = $pdo->prepare($sql_ventas_mes);
    $stmt_ventas_mes->execute();
    $ventas_mes = $stmt_ventas_mes->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error al obtener datos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informes - ProBikeXtreme</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Header -->
    <header class="bg-dark p-3">
        <?php include __DIR__ . '/../includes/header_admin.php'; ?>
    </header>

    <!-- Contenido principal -->
    <div class="container mt-5 flex-grow-1">
        <h2 class="text-center mb-4">Informes del Sistema</h2>

        <!-- Mensaje de error o éxito -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-warning">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Sección de Altas/Bajas de Usuarios -->
        <div class="card-admin mb-4">
            <div class="card-header form-container-second text-white">
                <h5 class="card-title mb-0">Altas/Bajas de Usuarios</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['estado']); ?></td>
                                <td><?php echo intval($usuario['total']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sección de Altas/Bajas de Artículos -->
        <div class="card-admin mb-4">
            <div class="card-header form-container-second text-white">
                <h5 class="card-title mb-0">Altas/Bajas de Artículos</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articulos as $articulo): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($articulo['estado']); ?></td>
                                <td><?php echo intval($articulo['total']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sección de Estadísticas de Pedidos -->
        <div class="card-admin mb-4">
            <div class="card-header form-container-second text-white">
                <h5 class="card-title mb-0">Estadísticas de Pedidos</h5>
            </div>
            <div class="card-body">
                <p><strong>Total de Pedidos:</strong> <?php echo isset($pedidos['total_pedidos']) ? intval($pedidos['total_pedidos']) : '0'; ?></p>
                <p><strong>Ingresos Totales:</strong> <?php echo number_format($pedidos['ingresos_totales'], 2); ?> €</p>
            </div>
        </div>

        <!-- Sección de Ventas del Mes -->
        <div class="card-admin mb-4">
            <div class="card-header form-container-second text-white">
                <h5 class="card-title mb-0">Ventas del Mes</h5>
            </div>
            <div class="card-body">
                <p><strong>Ingresos del Mes:</strong> <?php echo number_format($ventas_mes['ingresos_mes'], 2); ?> €</p>
            </div>
        </div>

        <!-- Botón de Volver -->
        <div class="text-end mt-4 mb-4">
            <a href="../administrador/index_administrador.php" class="btn btn-secondary">Volver al Panel de Administrador</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>

</body>
</html>
