<?php
// Iniciar sesión
session_start();

// Incluimos conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Verificamos si se recibió el ID del producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $producto_id = (int)$_POST['id'];

    // Validamos que el producto existe, está activo y tiene stock disponible
    try {
        $sql = "SELECT * FROM products WHERE id = :id AND estado = 'activo'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $producto_id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            $_SESSION['error'] = 'El producto no está disponible.';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }

        // Verificamos si el stock es 0
        if ($producto['stock'] == 0) {
            $_SESSION['error'] = 'Este producto está fuera de stock.';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }

        // Manejamos carrito según el tipo de usuario
        if (isset($_SESSION['id']) && $_SESSION['role'] === 'cliente') {
            try {
                $sql_carrito = "INSERT INTO carrito (usuario_id, producto_id, cantidad) 
                                VALUES (:usuario_id, :producto_id, 1)
                                ON DUPLICATE KEY UPDATE cantidad = cantidad + 1";
                $stmt_carrito = $pdo->prepare($sql_carrito);
                $stmt_carrito->execute([
                    ':usuario_id' => $_SESSION['id'],
                    ':producto_id' => $producto_id,
                ]);
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Error al insertar en el carrito: ' . $e->getMessage();
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }
        } else {
            // Si es visitante, guarda en la sesión
            if (!isset($_SESSION['carrito'])) {
                $_SESSION['carrito'] = [];
            }
            if (!isset($_SESSION['carrito'][$producto_id])) {
                $_SESSION['carrito'][$producto_id] = [
                    'cantidad' => 1,
                    'nombre' => $producto['nombre'],
                    'precio' => $producto['precio']
                ];
            } else {
                $_SESSION['carrito'][$producto_id]['cantidad']++;
            }
        }

        // Redirigimos al mismo lugar con éxito
        $_SESSION['success'] = 'Producto añadido al carrito.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error al añadir el producto al carrito: ' . $e->getMessage();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    $_SESSION['error'] = 'Acción no válida.';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
