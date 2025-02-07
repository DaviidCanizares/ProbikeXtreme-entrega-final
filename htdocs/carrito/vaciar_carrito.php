<?php
session_start();
include __DIR__ . '/../includes/conectar_db.php';

// Verificamos si el usuario es cliente o visitante
if (isset($_SESSION['id']) && $_SESSION['role'] === 'cliente') {
    // Vaciamos carrito en la base de datos
    try {
        $sql = "DELETE FROM carrito WHERE usuario_id = :usuario_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':usuario_id' => $_SESSION['id']]);
        $_SESSION['success'] = 'Carrito vaciado con éxito.';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error al vaciar el carrito: ' . $e->getMessage();
    }
} elseif (isset($_SESSION['carrito'])) {
    // Vaciamos carrito en la sesión
    unset($_SESSION['carrito']);
    $_SESSION['success'] = 'Carrito vaciado con éxito.';
}

// Redirigimos al carrito
header('Location: /carrito/carrito.php');
exit();
