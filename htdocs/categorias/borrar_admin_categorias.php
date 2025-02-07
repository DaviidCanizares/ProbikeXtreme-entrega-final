<?php
// Iniciar sesión
session_start();

// Verificamos si el usuario está autenticado como administrador
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['administrador', 'empleado'])) {
    header('Location: ../index.php');
    exit();
}

// Incluimos conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Verificamos si se proporciona un ID válido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Cambiamos el estado de la categoría a 'inactivo'
        $sql = "UPDATE categories SET estado = 'inactivo' WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        // Redirigimos con mensaje de éxito
        header('Location: categorias_admin_general.php?message=Categoría dada de baja correctamente');
        exit();
    } catch (PDOException $e) {
        die("Error al dar de baja la categoría: " . $e->getMessage());
    }
} else {
    header('Location: categorias_admin_general.php?message=ID inválido');
    exit();
}
