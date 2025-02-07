<?php

// Configuramos la bbdd
$host = "sql103.infinityfree.com"; //Aqui metemos el nombre del servidor
$dbname = "if0_38067316_probikextreme"; // aqui ponemos el nombre de la bbdd
$user = "if0_38067316"; // El usuario que nos da infinityfree
$password = "Zampa123"; // Y la contraseña

try {
    // Creamos la conexión con PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    // Configuramos el manejo de errores
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Manejamos errores de conexión
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
?>
