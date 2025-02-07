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
    $productoId = $_GET['id'];

    try {
        // Actualizamos el estado del producto a "inactivo"
        $sql = "UPDATE products SET estado = 'inactivo' WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $productoId]);

        // Redirigimos con mensaje de éxito
        header("Location: productos_admin_general.php?message=Producto%20dado%20de%20baja%20con%20éxito");
        exit();
    } catch (PDOException $e) {
        // Redirigimos con mensaje de error
        header("Location: productos_admin_general.php?error=Error%20al%20dar%20de%20baja%20el%20producto:%20" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Redirigimos si no se proporciona un ID válido
    header("Location: productos_admin_general.php?error=ID%20de%20producto%20no%20válido");
    exit();
}
?>
