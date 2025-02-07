<?php
// Iniciar sesión
session_start();

// Verificamos si el usuario está autenticado
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'cliente') {
    header('Location: ../index.php');
    exit();
}

// Incluimos conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Variables para mensajes y resultados
$message = '';

// Obtenemos los pedidos del usuario con sus productos
try {
    $sql_pedidos = "SELECT p.id AS pedido_id, p.fecha_pedido, p.total, p.estado, 
                           dp.producto_id, pr.nombre AS producto_nombre, dp.cantidad, dp.precio
                    FROM pedidos p
                    JOIN detalles_pedido dp ON p.id = dp.pedido_id
                    JOIN products pr ON dp.producto_id = pr.id
                    WHERE p.usuario_id = :usuario_id
                    ORDER BY p.fecha_pedido DESC";
    $stmt_pedidos = $pdo->prepare($sql_pedidos);
    $stmt_pedidos->execute([':usuario_id' => $_SESSION['id']]);
    $pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error al obtener los pedidos: " . $e->getMessage();
}

// Reorganizamos pedidos en un array agrupado
$pedidos_agrupados = [];
foreach ($pedidos as $pedido) {
    $pedido_id = $pedido['pedido_id'];
    if (!isset($pedidos_agrupados[$pedido_id])) {
        $pedidos_agrupados[$pedido_id] = [
            'pedido_id' => $pedido['pedido_id'],
            'fecha_pedido' => $pedido['fecha_pedido'],
            'total' => $pedido['total'],
            'estado' => $pedido['estado'],
            'productos' => []
        ];
    }
    $pedidos_agrupados[$pedido_id]['productos'][] = [
        'producto_id' => $pedido['producto_id'],
        'producto_nombre' => $pedido['producto_nombre'],
        'cantidad' => $pedido['cantidad'],
        'precio' => $pedido['precio']
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - ProBikeXtreme</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
 


</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Header -->
    <header class="bg-dark p-3">
        <?php include __DIR__ . '/../includes/header.php'; ?>
    </header>

    <!-- Contenido principal -->
    <div class="container mt-5 flex-grow-1">
        <h2 class="text-center mb-4">Mis Pedidos</h2>

        <!-- Mensaje de error o éxito -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

<!-- Listado de pedidos -->
<?php if (!empty($pedidos_agrupados)): ?>
    <div class="accordion " id="accordionPedidos">
        <?php foreach ($pedidos_agrupados as $pedido): ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?php echo $pedido['pedido_id']; ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $pedido['pedido_id']; ?>" aria-expanded="false">
                        Pedido #<?php echo htmlspecialchars($pedido['pedido_id']); ?> - <?php echo htmlspecialchars($pedido['fecha_pedido']); ?>
                    </button>
                </h2>
                <div id="collapse<?php echo $pedido['pedido_id']; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $pedido['pedido_id']; ?>" data-bs-parent="#accordionPedidos">
                    <div class="accordion-body">
                        <!-- Estado del pedido -->
                        <p>
                            <strong>Estado:</strong> 
                            <span class="badge 
                                <?php 
                                switch ($pedido['estado']) {
                                    case 'pendiente':
                                        echo 'bg-warning';
                                        break;
                                    case 'procesado':
                                        echo 'bg-info';
                                        break;
                                    case 'enviado':
                                        echo 'bg-success';
                                        break;
                                    case 'cancelado':
                                        echo 'bg-danger';
                                        break;
                                    default:
                                        echo 'bg-secondary';
                                }
                                ?>">
                                <?php echo htmlspecialchars($pedido['estado']); ?>
                            </span>
                        </p>
                        <p><strong>Total:</strong> €<?php echo number_format($pedido['total'], 2); ?></p>
                        <h5>Productos comprados:</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio unitario</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedido['productos'] as $producto): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($producto['producto_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($producto['cantidad']); ?></td>
                                        <td>€<?php echo number_format($producto['precio'], 2); ?></td>
                                        <td>€<?php echo number_format($producto['cantidad'] * $producto['precio'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if ($pedido['estado'] !== 'enviado' && $pedido['estado'] !== 'cancelado'): ?>
                            <!-- Botón para cancelar el pedido -->
                            <form action="/pedidos/cancelar_pedido.php" method="post" onsubmit="return confirm('¿Estás seguro de cancelar este pedido?');">
                                <input type="hidden" name="pedido_id" value="<?php echo $pedido['pedido_id']; ?>">
                                <button type="submit" class="btn btn-danger">Cancelar Pedido</button>
                            </form>
                        <?php else: ?>
                            <p class="text-muted">Este pedido no se puede cancelar.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="text-muted text-center">No tienes pedidos realizados.</p>
<?php endif; ?>


        <!-- Botón de Volver -->
        <div class="text-end mt-4 mb-4">
            <a href="../cliente/index_cliente.php" class="btn btn-secondary">Volver al Inicio</a>
        </div>
    </div> <!-- Cierre del container -->

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>

</body>
</html>