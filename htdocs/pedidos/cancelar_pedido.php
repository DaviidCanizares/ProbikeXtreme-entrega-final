<?php
session_start();
include __DIR__ . '/../includes/conectar_db.php';

// Verificamos que el usuario esté autenticado y sea un cliente
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'cliente') {
    header("Location: /auth/login.php");
    exit();
}

// Verificamos que se haya enviado el formulario por POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /cliente/mis_pedidos.php");
    exit();
}

// Recogemos el pedido_id enviado por POST
$pedido_id = isset($_POST['pedido_id']) ? (int)$_POST['pedido_id'] : 0;
if ($pedido_id <= 0) {
    $_SESSION['error'] = "Pedido no válido.";
    header("Location: /cliente/mis_pedidos.php");
    exit();
}

try {
    // Iniciar transacción
    $pdo->beginTransaction();

    // Obtenemos todos los productos y cantidades del pedido (de detalles_pedido)
    $sqlDetalles = "SELECT producto_id, cantidad FROM detalles_pedido WHERE pedido_id = :pedido_id";
    $stmtDetalles = $pdo->prepare($sqlDetalles);
    $stmtDetalles->execute([':pedido_id' => $pedido_id]);
    $detalles = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

    if (empty($detalles)) {
        throw new Exception("No se encontraron productos en el pedido.");
    }

    // Para cada producto del pedido, sumar la cantidad al stock
    foreach ($detalles as $detalle) {
        $sqlActualizarStock = "UPDATE products SET stock = stock + :cantidad WHERE id = :producto_id";
        $stmtActualizarStock = $pdo->prepare($sqlActualizarStock);
        $stmtActualizarStock->execute([
            ':cantidad' => $detalle['cantidad'],
            ':producto_id' => $detalle['producto_id']
        ]);
    }

    // Podemos eliminar los detalles del pedido o marcarlo como cancelado.
   
    $sqlActualizarPedido = "UPDATE pedidos SET estado = 'cancelado' WHERE id = :pedido_id";
    $stmtActualizarPedido = $pdo->prepare($sqlActualizarPedido);
    $stmtActualizarPedido->execute([':pedido_id' => $pedido_id]);

    //Confirmamos transacción
    $pdo->commit();

    $_SESSION['success'] = "Pedido cancelado correctamente y stock actualizado.";
    header("Location: /cliente/mis_pedidos.php");
    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error al cancelar el pedido: " . $e->getMessage();
    header("Location: /cliente/mis_pedidos.php");
    exit();
}
?>
