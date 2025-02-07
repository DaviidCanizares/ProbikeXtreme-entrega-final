<?php
// Iniciar sesión
session_start();

// Verificamos si el usuario es administrador
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['administrador', 'empleado'])) {
    header('Location: ../index.php');
    exit();
}

// Incluimos conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Verificamos si se proporcionó el ID del producto
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        // Actualizamos el estado del producto a 'activo'
        $sql = "UPDATE products SET estado = 'activo' WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        // Redirigimos con un mensaje de éxito
        $_SESSION['message'] = "Producto activado correctamente.";
        header("Location: productos_admin_general.php");
        exit();
    } catch (PDOException $e) {
        // Redirigimos con un mensaje de error
        $_SESSION['message'] = "Error al activar el producto: " . $e->getMessage();
        header("Location: productos_admin_general.php");
        exit();
    }
} else {
    // Redirigimos si no se proporciona un ID válido
    $_SESSION['message'] = "ID de producto no válido.";
    header("Location: productos_admin_general.php");
    exit();
}
