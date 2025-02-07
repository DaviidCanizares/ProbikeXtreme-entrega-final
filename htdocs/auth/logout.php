<?php
// Iniciar sesión
session_start();

// Destruimos todas las sesiones

session_destroy();

// Redirigimos al index principal
header("Location: ../index.php");
exit();
?>

