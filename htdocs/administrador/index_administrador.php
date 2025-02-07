<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - ProBikeXtreme</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>

<body class="d-flex flex-column min-vh-100">
    <header>
        <?php include __DIR__ . '/../includes/header_admin.php'; ?>
    </header>

    <!-- Main wrapper -->
    <div class="container mt-5 flex-grow-1">
        <h2 class="text-center mb-4">Panel de Administración</h2>

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

            <!-- Gestionamos Clientes -->
            <div class="col">
                <div class="card-admin text-center">
                    <div class="card-body">
                        <h5 class="card-title">Gestionar Clientes</h5>
                        <p class="card-text">Administra la información de los clientes.</p>
                        <a href="../cliente/clientes_admin_general.php" class="btn btn-second-color">Gestionar Clientes</a>
                    </div>
                </div>
            </div>

            <!-- Gestionamos Empleados -->
            <div class="col">
                <div class="card-admin text-center">
                    <div class="card-body">
                        <h5 class="card-title">Gestionar Empleados</h5>
                        <p class="card-text">Registra, edita y organiza a los empleados.</p>
                        <a href="../empleado/empleado_admin_general.php" class="btn btn-second-color">Gestionar Empleados</a>
                    </div>
                </div>
            </div>

            <!-- Obtenemos informes -->
            <div class="col">
                <div class="card-admin text-center">
                    <div class="card-body">
                        <h5 class="card-title">Informes</h5>
                        <p class="card-text">Consulta informes de ventas y gestión.</p>
                        <a href="index_informes.php" class="btn btn-second-color">Ver Informes</a>
                    </div>
                </div>
            </div>

            <!-- Gestionamos Categorías -->
            <div class="col">
                <div class="card-admin text-center">
                    <div class="card-body">
                        <h5 class="card-title">Gestionar Categorías</h5>
                        <p class="card-text">Administra las categorías y subcategorías del sistema.</p>
                        <a href="../categorias/categorias_admin_general.php" class="btn btn-second-color">Gestionar Categorías</a>
                    </div>
                </div>
            </div>
            <!-- Gestionamos Pedidos -->
            <div class="col">
                <div class="card-admin text-center">
                    <div class="card-body">
                        <h5 class="card-title">Gestionar Pedidos</h5>
                        <p class="card-text">Administra y supervisa los pedidos realizados.</p>
                        <a href="../pedidos/pedidos_admin_general.php" class="btn btn-second-color">Gestionar Pedidos</a>
                    </div>
                </div>
            </div>
            <!-- Gestionamos Pedidos -->
    <div class="col">
        <div class="card-admin text-center">
            <div class="card-body">
                <h5 class="card-title">Gestionar Devoluciones</h5>
                <p class="card-text">Supervisa las devoluciones.</p>
                <a href="../administrador/listado_devoluciones.php" class="btn btn-second-color">Gestionar Devoluciones</a>
            </div>
        </div>
    </div>

        </div>


        <!-- Botón de Logout -->
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