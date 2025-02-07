<?php
// Iniciar sesión
session_start();

// Incluimos el archivo de conexión a la base de datos
include __DIR__ . '/includes/conectar_db.php';

// Verificamos si el usuario ya está logueado y redirigirlo a su sección correspondiente
if (isset($_SESSION['role'])) {
    $currentPage = basename($_SERVER['SCRIPT_NAME']);
    switch ($_SESSION['role']) {
        case 'cliente':
            if ($currentPage !== 'index_cliente.php') {
                header('Location: cliente/index_cliente.php');
                exit();
            }
            break;
        case 'empleado':
            if ($currentPage !== 'index_empleado.php') {
                header('Location: empleado/index_empleado.php');
                exit();
            }
            break;
        case 'administrador':
            if ($currentPage !== 'index_administrador.php') {
                header('Location: administrador/index_administrador.php');
                exit();
            }
            break;
    }
}

// Inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $password = $_POST['password'] ?? '';
    $error = '';

    try {
        // Buscamos el usuario en la base de datos
        $sql_user = "SELECT idPrimaria, role, password FROM users WHERE nombre = :nombre";
        $stmt_user = $pdo->prepare($sql_user);
        $stmt_user->execute([':nombre' => $nombre]);
        $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Configuramos la sesión según el rol
            $_SESSION['id'] = $user['idPrimaria'];
            $_SESSION['role'] = $user['role'];

            switch ($user['role']) {
                case 'cliente':
                    header('Location: cliente/index_cliente.php');
                    break;
                case 'empleado':
                    header('Location: empleado/index_empleado.php');
                    break;
                case 'administrador':
                    header('Location: administrador/index_administrador.php');
                    break;
            }
            exit();
        } else {
            $error = 'Credenciales incorrectas.';
        }
    } catch (PDOException $e) {
        $error = 'Error al verificar las credenciales: ' . $e->getMessage();
    }
}

// Obtenemos los productos de la base de datos
try {
    $sql_products = "SELECT nombre, descripcion, precio, imagen, stock FROM products LIMIT 10";
    $stmt_products = $pdo->query($sql_products);
    $products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener los productos: " . $e->getMessage());
}

// Incluimos la lógica del inicio de sesión
include __DIR__ . '/auth/login.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>ProBikeXtreme</title>
    <?php include __DIR__ . '/includes/enlaces_bootstrap.php'; ?>
</head>

<body>
    <header>
        <?php include __DIR__ . '/includes/header.php'; ?>
    </header>

    <!-- Main content -->
    <div class="content container-fluid d-flex flex-column">
        <div class="row flex-grow-1" style="height: 100%;">
            <!-- Left section -->
            <aside class="col-md-2  p-3"">
                <?php include __DIR__ . '/includes/leftAside_dinamico.php'; ?>
            </aside>

            <!-- Central section -->
            <section class="  col-md-8"
                <?php include __DIR__ . '/includes/body_dinamico.php'; ?>
            </section>

            <!-- Right section -->
            <aside class="col-md-2  p-3">
            
                <?php include __DIR__ . '/includes/rightAside_dinamico.php'; ?>

            </aside>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <?php include __DIR__ . '/includes/footer.php'; ?>
    </footer>
</body>

</html>