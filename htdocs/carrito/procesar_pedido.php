<?php
session_start();

// Conectamos a la base de datos
include __DIR__ . '/../includes/conectar_db.php'; 

// Verificamos si el usuario está logueado y es un cliente
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'cliente') {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Obtenemos los datos del carrito enviados en formato JSON a través de php://input
$input = json_decode(file_get_contents('php://input'), true);
$carrito = $input['carrito'] ?? [];
$paypal_order_id = $input['paypal_order_id'] ?? '';

// Verificamos que el carrito no esté vacío
if (empty($carrito)) {
    echo json_encode(['success' => false, 'message' => 'Carrito vacío']);
    exit;
}

// Calculamos el total de la compra sumando el precio por la cantidad de cada producto
$total = 0;
foreach ($carrito as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

try {
    // Iniciamos una transacción para asegurar la atomicidad de las operaciones
    $pdo->beginTransaction();

    // Insertamos el pedido en la tabla pedidos, asignándole estado 'procesado'
    $sql_pedido = "INSERT INTO pedidos (usuario_id, total, estado, paypal_order_id) 
                   VALUES (:usuario_id, :total, 'procesado', :paypal_order_id)";
    $stmt_pedido = $pdo->prepare($sql_pedido);
    $stmt_pedido->execute([
        ':usuario_id' => $_SESSION['id'],
        ':total' => $total,
        ':paypal_order_id' => $paypal_order_id
    ]);

    // Obtenemos el ID del pedido recién insertado
    $pedido_id = $pdo->lastInsertId();

    // Preparamos las consultas para insertar cada detalle y actualizar el stock
    $sql_detalles = "INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio) 
                     VALUES (:pedido_id, :producto_id, :cantidad, :precio)";
    $stmt_detalles = $pdo->prepare($sql_detalles);

    $sql_stock = "UPDATE products SET stock = stock - :cantidad WHERE id = :id AND stock >= :cantidad";
    $stmt_stock = $pdo->prepare($sql_stock);

    // Por cada producto en el carrito, insertamos su detalle y restamos el stock
    foreach ($carrito as $item) {
        // Insertamos el detalle del pedido
        $stmt_detalles->execute([
            ':pedido_id' => $pedido_id,
            ':producto_id' => $item['id'],
            ':cantidad' => $item['cantidad'],
            ':precio' => $item['precio']
        ]);
        // Restamos el stock del producto, siempre que haya suficiente stock
        $stmt_stock->execute([
            ':cantidad' => $item['cantidad'],
            ':id' => $item['id']
        ]);

        // Si no se afectó ninguna fila, significa que no hay suficiente stock
        if ($stmt_stock->rowCount() === 0) {
            // Obtenemos el stock actual del producto
            $sql_check_stock = "SELECT stock FROM products WHERE id = :id";
            $stmt_check = $pdo->prepare($sql_check_stock);
            $stmt_check->execute([':id' => $item['id']]);
            $stock_actual = $stmt_check->fetchColumn();
            throw new Exception("Stock insuficiente para el producto '" . $item['nombre'] . "'. Solo quedan " . $stock_actual . " unidades disponibles.");
        }
    }

    // Vaciamos el carrito del usuario en la base de datos, ya que el pedido se ha completado
    $sql_vaciar_carrito = "DELETE FROM carrito WHERE usuario_id = :usuario_id";
    $stmt_vaciar = $pdo->prepare($sql_vaciar_carrito);
    $stmt_vaciar->execute([':usuario_id' => $_SESSION['id']]);
    
    // Confirmamos la transacción, ya que todas las operaciones se han ejecutado correctamente
    $pdo->commit();

    // Devolvemos un mensaje de éxito en formato JSON
    echo json_encode(['success' => true, 'message' => 'Pedido registrado, stock actualizado y carrito vaciado.']);
} catch (Exception $e) {
    // En caso de error, revertimos la transacción y devolvemos un mensaje de error
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
