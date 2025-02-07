<?php
// Iniciar sesión
session_start();
include __DIR__ . '/../includes/conectar_db.php';

// Verificamos si el ID del producto está configurado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    if (isset($_SESSION['id']) && $_SESSION['role'] === 'cliente') {
        // Cliente registrado: eliminar de la base de datos
        try {
            $sql = "DELETE FROM carrito WHERE usuario_id = :usuario_id AND producto_id = :producto_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $_SESSION['id'],
                ':producto_id' => $id,
            ]);

            // Redirigimos con mensaje de éxito
            header('Location: /carrito/carrito.php?mensaje=Articulo eliminado');
            exit();
        } catch (PDOException $e) {
            // Redirigimos con mensaje de error
            header('Location: /carrito/carrito.php?error=Error al eliminar el artículo');
            exit();
        }
    } elseif (isset($_SESSION['carrito'])) {
        // Visitante: eliminar de la sesión
        if (isset($_SESSION['carrito'][$id])) {
            unset($_SESSION['carrito'][$id]);
            header('Location: /carrito/carrito.php?mensaje=Articulo eliminado');
            exit();
        } else {
            header('Location: /carrito/carrito.php?error=Articulo no encontrado');
            exit();
        }
    }
}

// Redirigimos si no se recibe un ID válido
header('Location: /carrito/carrito.php?error=Solicitud inválida');
exit();
