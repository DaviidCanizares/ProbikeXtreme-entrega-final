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

// Verificamos si se proporcionó un ID de empleado/administrador válido
$id = $_GET['id'] ?? null;
$message = '';

if ($id) {
    try {
        // Cambiamos el estado a 'inactivo' para usuarios cuyo rol sea 'empleado' o 'administrador'
        $sql = "UPDATE users SET estado = 'inactivo' WHERE id = :id AND role IN ('empleado', 'administrador')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount()) {
            $message = "Usuario desactivado correctamente.";
        } else {
            $message = "No se encontró el usuario o ya estaba inactivo.";
        }
    } catch (PDOException $e) {
        $message = "Error al desactivar el usuario: " . $e->getMessage();
    }
} else {
    $message = "ID de usuario no válido.";
}

// Redirigimos de nuevo a la página de gestión de empleados
header("Location: empleado_admin_general.php?message=" . urlencode($message));
exit();
?>
