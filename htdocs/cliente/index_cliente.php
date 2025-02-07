<?php
// Iniciar sesión
session_start();

// Verificamos si el usuario está autenticado como cliente
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'cliente') {
    header('Location: ../index.php');
    exit();
}

// Incluimos el archivo de conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Inicializamos variables para almacenar los datos del cliente
$nombre = $email = $telefono = $direccion = '';
$message = ''; // Mensaje de éxito o error

// Obtenemos los datos del cliente desde la tabla users
try {
    $sql = "SELECT nombre, email, telefono, direccion FROM users WHERE id = :id AND role = 'cliente'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $_SESSION['id']]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        $nombre = $cliente['nombre'];
        $email = $cliente['email'];
        $telefono = $cliente['telefono'] ?? '';
        $direccion = $cliente['direccion'] ?? '';
    } else {
        echo "Cliente no encontrado.";
        exit();
    }
} catch (PDOException $e) {
    die("Error al obtener los datos del cliente: " . $e->getMessage());
}

// Procesamos la actualización de datos si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars($_POST['nombre']);
    $email = htmlspecialchars($_POST['email']);
    $telefono = htmlspecialchars($_POST['telefono']);
    $direccion = htmlspecialchars($_POST['direccion']);

    try {
        $sql = "UPDATE users SET nombre = :nombre, email = :email, telefono = :telefono, direccion = :direccion WHERE id = :id AND role = 'cliente'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':telefono' => $telefono,
            ':direccion' => $direccion,
            ':id' => $_SESSION['id']
        ]);

        $message = "Datos actualizados correctamente.";
    } catch (PDOException $e) {
        $message = "Error al actualizar los datos: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProBikeXtreme - Cliente</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>

<body>
    <header>
        <?php include __DIR__ . '/../includes/header.php'; ?>
    </header>

    <!-- Main content -->
    <div class="content container-fluid">
        <div class="row" >
            <!-- Left section -->
            <aside class="col-md-2  p-3">
            <?php include __DIR__ . '/../includes/leftAside_dinamico.php'; ?>
            </aside>

            <!-- Central section -->
                <section class=" col-md-8">
                <?php include __DIR__ . '/../includes/body_dinamico.php'; ?>
                </section>


            <!-- Right section -->
            <aside class="col-md-2  p-3">
                <h3 class="text-white">Bienvenido <?php echo htmlspecialchars($_SESSION['nombre']?? 'Cliente');?></h3>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="mis_datos.php" class="nav-link text-white">Mis datos</a>
                    </li>
                    <li class="nav-item">
                        <a href="mis_pedidos.php" class="nav-link text-white">Mis pedidos</a>
                    </li>
                    <li class="nav-item">
                        <a href="/administrador/devoluciones.php" class="nav-link text-white">Devoluciones</a>
                    </li>
                </ul>
                <!-- Botón de cerrar sesión -->
                <a href="../auth/logout.php" class="btn btn-second-color w-100 mt-3">Cerrar sesión</a>
            </aside>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center p-3">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
