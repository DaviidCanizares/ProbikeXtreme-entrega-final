<?php
// Iniciar sesión
session_start();

// Verificamos si el usuario está autenticado como administrador
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'administrador') {
    header('Location: ../index.php');
    exit();
}
// Incluimos conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Verificamos si se proporcionó un ID de cliente válido
$id = $_GET['id'] ?? null;
$message = '';

if ($id) {
    try {
        // Cambiamos el estado del cliente a 'inactivo'
        $sql = "UPDATE users SET estado = 'inactivo' WHERE id = :id AND role = 'cliente'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount()) {
            $message = "Cliente desactivado correctamente.";
        } else {
            $message = "No se encontró el cliente o ya estaba inactivo.";
        }
    } catch (PDOException $e) {
        $message = "Error al desactivar el cliente: " . $e->getMessage();
    }
} else {
    $message = "ID de cliente no válido.";
}

// Redirigimos de nuevo a la página de gestión de clientes
header("Location: clientes_admin_general.php?message=" . urlencode($message));
exit();
