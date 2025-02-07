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

// Inicializamos mensaje
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtenemos datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $imagen = $_POST['imagen'] ?? '';
    // Definimos un valor por defecto si el campo no está en el formulario
    $stock = $_POST['stock'] ?? 0; 
    $categoria_id = $_POST['categoria_id'] ?? null;
    $tipo_producto = $_POST['tipo_producto'] ?? null;
    $transmision = $_POST['transmision'] ?? null;
    $peso = $_POST['peso'] ?? null;
    $estado = $_POST['estado'] ?? null;
    $marca_id = $_POST['marca_id'] ?? null;

    try {
        // Insertamos producto en la base de datos
        $sql = "INSERT INTO products (nombre, descripcion, precio, stock, categoria_id, tipo_producto, transmision, peso, estado, imagen, marca_id) 
        VALUES (:nombre, :descripcion, :precio, :stock, :categoria_id, :tipo_producto, :transmision, :peso, :estado, :imagen, :marca_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => htmlspecialchars($nombre),
            ':descripcion' => htmlspecialchars($descripcion),
            ':precio' => $precio,
            ':stock' => $stock,
            ':categoria_id' => $categoria_id,
            ':tipo_producto' => $tipo_producto,
            ':transmision' => $transmision,
            ':peso' => $peso,
            ':estado' => $estado,
            ':imagen' => htmlspecialchars($imagen),
            ':marca_id' => $marca_id
        ]);

        $message = "Producto añadido correctamente.";
    } catch (PDOException $e) {
        $message = "Error al añadir el producto: " . $e->getMessage();
    }
}

?>
