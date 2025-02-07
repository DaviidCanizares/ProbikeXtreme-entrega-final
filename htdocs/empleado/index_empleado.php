<?php
// Iniciar sesión
session_start();

// Verificamos si el usuario está autenticado como empleado
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'empleado') {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Empleado - ProBikeXtreme</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>

<body class="d-flex flex-column min-vh-100">
    <header>
        <?php include __DIR__ . '/../includes/header_admin.php'; ?>
    </header>

    <!-- Main wrapper -->
    <div class="container mt-5 flex-grow-1">
        <h2 class="text-center mb-4">Panel de Empleado</h2>

        <!-- Cards -->
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <!-- Gestionamos Productos -->
            <div class="col">
                <div class="card-admin text-center">
                    <div class="card-body">
                        <h5 class="card-title">Gestionar Productos</h5>
                        <p class="card-text">Gestiona y organiza los productos del catálogo.</p>
                        <a href="../products/productos_admin_general.php" class="btn btn-second-color">Gestionar Productos</a>
                    </div>
                </div>
            </div>

            <!-- Gestionamos Pedidos -->
            <div class="col">
                <div class="card-admin text-center">
                    <div class="card-body">
                        <h5 class="card-title">Gestionar Pedidos</h5>
                        <p class="card-text">Administra la información de los pedidos.</p>
                        <a href="../pedidos/pedidos_admin_general.php" class="btn btn-second-color">Gestionar Pedidos</a>
                    </div>
                </div>
            </div>

            <!-- Gestionamos Categorías -->
            <div class="col">
                <div class="card-admin text-center">
                    <div class="card-body">
                        <h5 class="card-title">Gestionar Categorías</h5>
                        <p class="card-text">Administra las categorías y las subcategorías.</p>
                        <a href="../categorias/categorias_admin_general.php" class="btn btn-second-color">Gestionar Categorías</a>
                    </div>
                </div>
            </div>

            <!-- Gestionamos Devoluciones -->
            <div class="col">
                <div class="card-admin text-center">
                    <div class="card-body">
                        <h5 class="card-title">Gestionar Devoluciones</h5>
                        <p class="card-text">Administra las devoluciones y las políticas relacionadas.</p>
                        <a href="../administrador/listado_devoluciones.php" class="btn btn-second-color">Gestionar Devoluciones</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logout -->
        <div class="text-end mt-4 mb-4">
            <a href="../auth/logout.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>
</body>

</html>
