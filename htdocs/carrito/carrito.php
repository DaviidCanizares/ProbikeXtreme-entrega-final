<?php
session_start();
include __DIR__ . '/../includes/conectar_db.php';

$carrito = [];
$total = 0;

// Si el usuario es cliente, cargamos desde la base de datos
if (isset($_SESSION['id']) && $_SESSION['role'] === 'cliente') {
    try {
        $sql_carrito = "SELECT p.id, 
                               p.nombre, 
                               p.precio, 
                               c.cantidad,
                               promo.descuento,
                               (p.precio - (p.precio * promo.descuento / 100)) AS precio_con_descuento
                        FROM carrito c 
                        JOIN products p ON c.producto_id = p.id 
                        LEFT JOIN promociones promo ON p.id = promo.producto_id
                        WHERE c.usuario_id = :usuario_id";
        $stmt_carrito = $pdo->prepare($sql_carrito);
        $stmt_carrito->execute([':usuario_id' => $_SESSION['id']]);
        $carrito = $stmt_carrito->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error al cargar el carrito: " . $e->getMessage();
    }
       } elseif (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $id => $item) {
        // Consultamos la base de datos para obtener el descuento del producto
        $descuento = 0;
        try {
            $sql_descuento = "SELECT descuento FROM promociones WHERE producto_id = :producto_id";
            $stmt_descuento = $pdo->prepare($sql_descuento);
            $stmt_descuento->execute([':producto_id' => $id]);
            $promocion = $stmt_descuento->fetch(PDO::FETCH_ASSOC);

            if ($promocion) {
                $descuento = $promocion['descuento'];
            }
        } catch (PDOException $e) {
            echo "Error al obtener descuento: " . $e->getMessage();
        }

        // Calculamos el precio con descuento
        $precio_con_descuento = $item['precio'] - ($item['precio'] * $descuento / 100);

        // Guardamos en el carrito
        $carrito[] = [
            'id' => $id,
            'nombre' => $item['nombre'],
            'precio' => $item['precio'],
            'cantidad' => $item['cantidad'],
            'descuento' => $descuento,
            'precio_con_descuento' => $precio_con_descuento
        ];
    }
}



// Calculamos el total
foreach ($carrito as $item) {
    if (!empty($item['descuento']) && $item['descuento'] > 0) {
        $precio_final = $item['precio_con_descuento'];
    } else {
        $precio_final = $item['precio'];
    }
    $total += $precio_final * $item['cantidad'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - ProBikeXtreme</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
    <script>
        const carrito = <?php echo json_encode($carrito); ?>;
    </script>
</head>
<body>
    <!-- Header -->
    <header>
        <?php include __DIR__ . '/../includes/header.php'; ?>
    </header>

    <!-- Contenido principal -->
    <div class="container mt-5">
        <h2 class="text-center">Carrito de Compras</h2>

        <?php if (!empty($carrito)): ?>
            <table class="table table-striped mt-4">
                <thead class="table-dark">
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($carrito as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                            <td>
                                <?php if (!empty($item['descuento']) && $item['descuento'] > 0): ?>
                                    <del><?php echo number_format($item['precio'], 2); ?> €</del>
                                    <br>
                                    <span class="text-success"><?php echo number_format($item['precio_con_descuento'], 2); ?> €</span>
                                    <br>
                                    <small>(Descuento: <?php echo number_format($item['descuento'], 2); ?>%)</small>
                                <?php else: ?>
                                    <?php echo number_format($item['precio'], 2); ?> €
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="/carrito/actualizar_carrito.php" method="post" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="cantidad" value="<?php echo $item['cantidad']; ?>" class="form-control d-inline w-50">
                                    <button type="submit" name="modificar" class="btn btn-sm btn-warning">Actualizar</button>
                                </form>
                            </td>
                            <td>
                                <?php 
                                $precio_final = (!empty($item['descuento']) && $item['descuento'] > 0) ? $item['precio_con_descuento'] : $item['precio'];
                                echo number_format($precio_final * $item['cantidad'], 2); 
                                ?> €
                            </td>
                            <td>
                                <form action="/carrito/eliminar_articulo_carrito.php" method="post" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td><strong><?php echo number_format($total, 2); ?> €</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        <?php else: ?>
            <div class="alert alert-info text-center">Tu carrito está vacío.</div>
        <?php endif; ?>

        <div class="d-flex justify-content-between mt-4 mb-4">
            <!-- Botón para vaciar el carrito -->
            <form action="/carrito/vaciar_carrito.php" method="post">
                <button type="submit" name="vaciar" class="btn btn-danger">Vaciar carrito</button>
            </form>

            <!-- Botón para volver al inicio -->
            <a href="<?php echo isset($_SESSION['role']) && $_SESSION['role'] === 'cliente' ? '/cliente/index_cliente.php' : '/index.php'; ?>" class="btn btn-second-color">Volver al inicio</a>

            <!-- Botón para finalizar compra -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'cliente'): ?>
                <a href="paypal.php" class="btn btn-first-color">Finalizar Compra</a>
            <?php else: ?>
                <a href="/auth/register.php" class="btn btn-first-color">Finalizar Compra</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="fixed-bottom">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>
</body>
</html>
