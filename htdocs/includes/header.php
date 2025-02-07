<header class="p-3 bg-dark w-100">
    <div class="d-flex justify-content-between align-items-center w-100">
        <!-- Logo completamente a la izquierda -->
        <div class="logo-container me-auto">
            <a href="../index.php" class="d-flex align-items-center">
                <img src="../assets/imagenes/logo.png" alt="Logo" class="img-fluid" style="max-width: 100px;">
            </a>
        </div>

        <!-- Carrito centrado -->
        <div class="cart-container mx-auto text-center">
            <?php
            $total_productos = 0;

            if (isset($_SESSION['id']) && $_SESSION['role'] === 'cliente') {
                include __DIR__ . '/../includes/conectar_db.php';
                $sql_carrito = "SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = :usuario_id";
                $stmt_carrito = $pdo->prepare($sql_carrito);
                $stmt_carrito->execute([':usuario_id' => $_SESSION['id']]);
                $total_productos = (int)$stmt_carrito->fetchColumn();
            } elseif (isset($_SESSION['carrito'])) {
                foreach ($_SESSION['carrito'] as $producto) {
                    $total_productos += $producto['cantidad'];
                }
            }
            ?>
            <a href="/carrito/carrito.php" class="cart-icon text-white d-flex align-items-center">
                <i class="bi bi-cart"></i>
                <span class="badge btn-second-color ms-2"><?php echo $total_productos; ?></span>
            </a>
        </div>

        <!-- Menú de navegación a la derecha -->
        <div class="nav-container ms-auto">
            <nav>
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link text-white px-3" href="../index.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white px-3" href="../promociones.php">Promociones</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white px-3" href="../index_sobreNosotros.php">Sobre nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white px-3" href="../contacto.php">Contacto</a>
                    </li>
                </ul>
            </nav>
        </div>
        
    </div>
    <!--Buscador por nombre-->
<div class="search-container mt-3 d-flex justify-content-center">
    <form action="../products/productos.php" method="GET" class="w-50">
        <div class="input-group">
            <input type="text" name="query" class="form-control" placeholder="Buscar productos..." required>
            <button class="btn btn-first-color" type="submit">Buscar</button>
        </div>
    </form>
</div>
</header>
