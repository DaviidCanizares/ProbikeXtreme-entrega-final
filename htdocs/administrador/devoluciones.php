<?php
// Iniciamos sesión
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

// Procesamos la devolución
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedido_id = $_POST['pedido_id'];
    $producto_id = $_POST['producto_id'];
    $cantidad = $_POST['cantidad'];

    try {
        // Iniciamos una transacción para asegurar la atomicidad
        $pdo->beginTransaction();

        //Verificamos que el pedido y el producto existen
        $sql_verificar = "SELECT cantidad FROM detalles_pedido WHERE pedido_id = :pedido_id AND producto_id = :producto_id";
        $stmt_verificar = $pdo->prepare($sql_verificar);
        $stmt_verificar->execute([':pedido_id' => $pedido_id, ':producto_id' => $producto_id]);
        $detalle_pedido = $stmt_verificar->fetch(PDO::FETCH_ASSOC);

        if (!$detalle_pedido || $detalle_pedido['cantidad'] < $cantidad) {
            throw new Exception("No se puede devolver más productos de los que se compraron.");
        }

        //Sumamos la cantidad devuelta al stock del producto
        $sql_actualizar_stock = "UPDATE products SET stock = stock + :cantidad WHERE id = :producto_id";
        $stmt_actualizar_stock = $pdo->prepare($sql_actualizar_stock);
        $stmt_actualizar_stock->execute([':cantidad' => $cantidad, ':producto_id' => $producto_id]);

        // Registramos la devolución en la tabla de devoluciones (incluyendo usuario_id)
        $sql_registrar_devolucion = "INSERT INTO devoluciones (pedido_id, usuario_id, producto_id, cantidad) VALUES (:pedido_id, :usuario_id, :producto_id, :cantidad)";
        $stmt_registrar_devolucion = $pdo->prepare($sql_registrar_devolucion);
        $stmt_registrar_devolucion->execute([
            ':pedido_id'   => $pedido_id,
            ':usuario_id'  => $_SESSION['id'],  // Agregamos el usuario que está realizando la devolución
            ':producto_id' => $producto_id,
            ':cantidad'    => $cantidad
        ]);

        // Confirmamos la transacción
        $pdo->commit();

        $message = "Devolución realizada con éxito. Se han devuelto $cantidad unidades del producto.";
    } catch (Exception $e) {
        // Revertimos la transacción en caso de error
        $pdo->rollBack();
        $message = "Error al procesar la devolución: " . $e->getMessage();
    }
}

// Obtenemos los pedidos del usuario para mostrar en el formulario
try {
    $sql_pedidos = "SELECT p.id AS pedido_id, pr.nombre AS producto_nombre, dp.producto_id, dp.cantidad 
                    FROM detalles_pedido dp
                    JOIN pedidos p ON dp.pedido_id = p.id
                    JOIN products pr ON dp.producto_id = pr.id
                    WHERE p.usuario_id = :usuario_id";
    $stmt_pedidos = $pdo->prepare($sql_pedidos);
    $stmt_pedidos->execute([':usuario_id' => $_SESSION['id']]);
    $pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error al obtener los pedidos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devoluciones - ProBikeXtreme</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Header -->
    <header class="bg-dark p-3">
        <?php include __DIR__ . '/../includes/header.php'; ?>
    </header>

    <!-- Contenido principal -->
    <div class="container mt-5 flex-grow-1">
        <h2 class="text-center mb-4">Devoluciones</h2>

        <!-- Mensaje de error o éxito -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de devolución -->
        <form method="POST" class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="pedido_id" class="form-label">Seleccionar Pedido</label>
                <select name="pedido_id" id="pedido_id" class="form-select" required>
                    <option value="">-- Seleccione un pedido --</option>
                    <?php foreach ($pedidos as $pedido): ?>
                        <option value="<?php echo htmlspecialchars($pedido['pedido_id']); ?>">
                            Pedido #<?php echo htmlspecialchars($pedido['pedido_id']); ?> - <?php echo htmlspecialchars($pedido['producto_nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="producto_id" class="form-label">Producto</label>
                <select name="producto_id" id="producto_id" class="form-select" required>
                    <option value="">-- Seleccione un producto --</option>
                    <?php foreach ($pedidos as $pedido): ?>
                        <option value="<?php echo htmlspecialchars($pedido['producto_id']); ?>">
                            <?php echo htmlspecialchars($pedido['producto_nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="cantidad" class="form-label">Cantidad a Devolver</label>
                <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" required>
            </div>

            <div class="col-md-12 text-end d-flex justify-content-around p-5">
                <a href="../cliente/index_cliente.php" class="btn btn-first-color p-1 mb-3">Volver a inicio</a>
                <button type="submit" class="btn btn-second-color p-1 mb-3">Devolver producto</button>
            </div>
        </form>
    </div> 

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>

</body>
</html>