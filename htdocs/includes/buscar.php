<?php
session_start();
include __DIR__ . '/../includes/conectar_db.php';

// Verificamos si se envió una búsqueda
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $busqueda = trim($_GET['query']);
    $busqueda_codificada = urlencode($busqueda); // Codifica caracteres especiales

    // Redirigimos a productos.php con el término de búsqueda
    header("Location: /products/productos.php?query=$busqueda_codificada");
    exit();
} else {
    // Si no hay término de búsqueda, redirigimos a la lista de productos general
    header("Location: /products/productos.php");
    exit();
}
