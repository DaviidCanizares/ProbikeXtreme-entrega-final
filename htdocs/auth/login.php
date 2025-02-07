<?php
// Iniciamos sesión
session_start();

// Incluimos el archivo de conexión a la base de datos
include 'conectar_db.php';

// Inicializamos mensaje de error o éxito
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Obtenemos datos del formulario
    $nombre = trim($_POST['nombre']);
    $password = trim($_POST['password']);

    if (!empty($nombre) && !empty($password)) {
        try {
            // Buscamos en la tabla users
            $sql_user = "SELECT id, nombre, password, role, estado FROM users WHERE nombre = :nombre";
            $stmt_user = $pdo->prepare($sql_user);
            $stmt_user->execute([':nombre' => $nombre]);
            $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if ($user['estado'] !== 'activo') {
                    $message = "Tu cuenta está inactiva. Por favor, contacta con el administrador.";
                } elseif (password_verify($password, $user['password'])) {
                    // Guardamos datos del usuario en la sesión
                    $_SESSION['id'] = $user['id'];
                    $_SESSION['nombre'] = $user['nombre'];
                    $_SESSION['role'] = $user['role'];

                   // Redirigimos según el rol
                        switch ($user['role']) {
                            case 'cliente':
                                header("Location: /cliente/index_cliente.php");
                                break;
                            case 'empleado':
                                header("Location: /empleado/index_empleado.php");
                                break;
                            case 'administrador':
                                header("Location: /administrador/index_administrador.php");
                                break;
                            default:
                                $message = "Rol de usuario desconocido.";
                            }
                            exit();
                    } else {
                        $message = "Nombre de usuario o contraseña incorrectos. Por favor, inténtalo de nuevo.";
                    }
                } else {
                    $message = "Nombre de usuario o contraseña incorrectos. Por favor, inténtalo de nuevo.";
                }
            } catch (PDOException $e) {
                $message = "Error al iniciar sesión: " . $e->getMessage();
            }
        } else {
            $message = "Por favor, completa todos los campos.";
    }
}
?>