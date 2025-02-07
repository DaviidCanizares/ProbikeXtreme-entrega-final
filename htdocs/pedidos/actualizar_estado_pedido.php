<?php
session_start();
include __DIR__ . '/../includes/conectar_db.php';

// Verificamos que el usuario sea administrador o empleado
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['administrador', 'empleado'])) {
    header('Location: ../index.php');
    exit();
}

// Verificamos que la solicitud sea POST y que se haya enviado el array "estado"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['estado']) && is_array($_POST['estado'])) {
    try {
        $pdo->beginTransaction();
        
        foreach ($_POST['estado'] as $pedido_id => $nuevo_estado) {
            // Consultamos el estado actual del pedido
            $sql_estado = "SELECT estado FROM pedidos WHERE id = :pedido_id";
            $stmt_estado = $pdo->prepare($sql_estado);
            $stmt_estado->execute([':pedido_id' => $pedido_id]);
            $estado_actual = $stmt_estado->fetchColumn();
            
            // Si el nuevo estado es "cancelado" y el estado actual no es "cancelado" ni "enviado"
            if ($nuevo_estado === 'cancelado' && $estado_actual !== 'cancelado' && $estado_actual !== 'enviado') {
                // Obtenemos los detalles del pedido (productos y cantidades)
                $sql_detalles = "SELECT producto_id, cantidad FROM detalles_pedido WHERE pedido_id = :pedido_id";
                $stmt_detalles = $pdo->prepare($sql_detalles);
                $stmt_detalles->execute([':pedido_id' => $pedido_id]);
                $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($detalles)) {
                    foreach ($detalles as $detalle) {
                        // Actualizamos el stock del producto sumándole la cantidad cancelada
                        $sql_update_stock = "UPDATE products SET stock = stock + :cantidad WHERE id = :producto_id";
                        $stmt_update_stock = $pdo->prepare($sql_update_stock);
                        $stmt_update_stock->execute([
                            ':cantidad'    => $detalle['cantidad'],
                            ':producto_id' => $detalle['producto_id']
                        ]);
                    }
                }
            }
            
            // Actualizamos el estado del pedido
            $sql_update_state = "UPDATE pedidos SET estado = :estado WHERE id = :pedido_id";
            $stmt_update_state = $pdo->prepare($sql_update_state);
            $stmt_update_state->execute([
                ':estado'    => $nuevo_estado,
                ':pedido_id' => $pedido_id
            ]);
        }
        
        $pdo->commit();
        $_SESSION['success'] = "Estados de los pedidos actualizados correctamente.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error al actualizar estados: " . $e->getMessage();
    }
}

header('Location: pedidos_admin_general.php');
exit();
?>
