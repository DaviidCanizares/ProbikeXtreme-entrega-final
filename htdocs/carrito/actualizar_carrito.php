<?php
session_start();
include __DIR__ . '/../includes/conectar_db.php';

// Verificamos que se haya enviado el formulario por POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /carrito/carrito.php");
    exit();
}

// Recogemos el ID del producto y la nueva cantidad desde POST
$producto_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$cantidad = isset($_POST['cantidad']) ? (int) $_POST['cantidad'] : 0;

// Validamos que el ID del producto sea válido
if ($producto_id <= 0) {
    $_SESSION['error'] = "Producto no válido.";
    header("Location: /carrito/carrito.php");
    exit();
}

// Si el usuario está autenticado como cliente, actualizamos en la base de datos
if (isset($_SESSION['id']) && $_SESSION['role'] === 'cliente') {
    try {
        if ($cantidad <= 0) {
            // Si la cantidad es cero o menor, eliminamos el artículo del carrito en BD
            $sql = "DELETE FROM carrito WHERE producto_id = :producto_id AND usuario_id = :usuario_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':producto_id' => $producto_id,
                ':usuario_id'  => $_SESSION['id']
            ]);
        } else {
            // Si la cantidad es mayor que cero, actualizamos la cantidad en la BD
            $sql = "UPDATE carrito SET cantidad = :cantidad WHERE producto_id = :producto_id AND usuario_id = :usuario_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':cantidad'    => $cantidad,
                ':producto_id' => $producto_id,
                ':usuario_id'  => $_SESSION['id']
            ]);
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al actualizar el carrito: " . $e->getMessage();
        header("Location: /carrito/carrito.php");
        exit();
    }
    header("Location: /carrito/carrito.php");
    exit();
}
// Caso visitante: actualizamos el carrito almacenado en sesión (si existe)
elseif (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {
    if ($cantidad <= 0) {
        // Si la cantidad es cero o menor, eliminamos el producto del carrito de sesión
        unset($_SESSION['carrito'][$producto_id]);
    } else {
        // Si existe el producto, actualizamos la cantidad
        if (isset($_SESSION['carrito'][$producto_id])) {
            $_SESSION['carrito'][$producto_id]['cantidad'] = $cantidad;
        } else {
            // Opcional: si el producto no existe, se podría agregar (dependiendo de la lógica de tu sitio)
            $_SESSION['carrito'][$producto_id] = [
                'nombre'   => 'Producto Desconocido', // O bien recuperarlo de la BD
                'precio'   => 0,                      // Se debe definir el precio si es posible
                'cantidad' => $cantidad
            ];
        }
    }
    header("Location: /carrito/carrito.php");
    exit();
} else {
    // Si no se cumple ninguna condición, redirigimos al carrito
    header("Location: /carrito/carrito.php");
    exit();
}
?>
