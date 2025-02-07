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

// Verificamos si se proporciona un ID válido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Actualizamos el estado del cliente a 'activo'
        $sql = "UPDATE users SET estado = 'activo' WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        // Redirigimos con mensaje de éxito
        header('Location: clientes_admin_general.php?message=Cliente activado con éxito');
        exit();
    } catch (PDOException $e) {
        die("Error al activar el cliente: " . $e->getMessage());
    }
} else {
    header('Location: clientes_admin_general.php?message=ID inválido');
    exit();
}
