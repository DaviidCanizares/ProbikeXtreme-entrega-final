<?php
// Verificamos si ya se incluyó la conexión a la base de datos
if (!isset($pdo)) {
    include __DIR__ . '/../includes/conectar_db.php';
}

// Configuramos la paginación
$productos_por_pagina = 4; // Número de productos por página
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $productos_por_pagina;

// Contamos el total de productos activos
try {
    $sql_total = "SELECT COUNT(*) as total FROM products WHERE estado = 'activo'";
    $stmt_total = $pdo->query($sql_total);
    $total_productos = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    die("Error al contar los productos: " . $e->getMessage());
}

// Calculamos el total de páginas
$total_paginas = ceil($total_productos / $productos_por_pagina);

// Consultamos los productos activos y, si tienen promoción, se unen sus datos
try {
    $sql_products = "SELECT p.*, promo.descuento, promo.temporada,
                        (p.precio - (p.precio * promo.descuento / 100)) AS precio_con_descuento
                     FROM products p
                     LEFT JOIN promociones promo ON p.id = promo.producto_id
                     WHERE p.estado = 'activo'
                     LIMIT :offset, :limit";
    $stmt_products = $pdo->prepare($sql_products);
    $stmt_products->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt_products->bindValue(':limit', $productos_por_pagina, PDO::PARAM_INT);
    $stmt_products->execute();
    $productos = $stmt_products->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar los productos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProBikeXtreme - Cliente</title>
    <?php include __DIR__ . '/includes/enlaces_bootstrap.php'; ?>
</head>
<body class="">
    <!-- Sección principal de productos -->
    <section class="container mt-5">
        <h2 class="text-white">Bienvenido a ProBikeXtreme</h2>
        <p class="text-white">Explora nuestra selección de productos para ciclistas.</p>

        <!-- Listado de productos -->
        <?php if (!empty($productos)): ?>
            <div class="row">
                <?php foreach ($productos as $producto): ?>
                    <div class="col-md-6 col-lg-3 mb-4 d-flex">
                        <div class="card d-flex flex-column">
                            <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" 
                                 class="card-img-top" 
                                 style="height: 200px; object-fit: cover;" 
                                 alt="Imagen de <?php echo htmlspecialchars($producto['nombre']); ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                                <!-- Mostramos el precio o un mensaje si no hay stock -->
                                <?php if ($producto['stock'] <= 0): ?>
                                    <p class="card-text text-danger"><strong>No queda stock</strong></p>
                                <?php else: ?>
                                    <?php if (!empty($producto['descuento'])): ?>
                                        <!-- Si tiene promoción -->
                                        <p class="card-text">
                                            <del>€<?php echo number_format($producto['precio'], 2); ?></del><br>
                                            <span class="text-success h5">€<?php echo number_format($producto['precio_con_descuento'], 2); ?></span><br>
                                            <small>(Descuento: <?php echo number_format($producto['descuento'], 2); ?>%)</small>
                                        </p>
                                    <?php else: ?>
                                        <!-- Sin promoción -->
                                        <p class="card-text"><strong>€<?php echo number_format($producto['precio'], 2); ?></strong></p>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <div class="mt-auto">
                                    <form method="POST" action="/carrito/añadirCarrito.php">
                                        <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                                        <button type="submit" name="añadir" class="btn btn-second-color w-100">Añadir al carrito</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">No hay productos disponibles en este momento.</p>
        <?php endif; ?>

        <!-- Paginación -->
        <nav aria-label="Paginación de productos">
            <ul class="pagination justify-content-center">
                <?php if ($pagina_actual > 1): ?>
                    <li class="page-item">
                        <a class="page-link btn-second-color" href="?pagina=<?php echo $pagina_actual - 1; ?>" aria-label="Anterior">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo ($pagina_actual === $i) ? 'active' : ''; ?>">
                        <a class="page-link btn-second-color" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($pagina_actual < $total_paginas): ?>
                    <li class="page-item">
                        <a class="page-link btn-second-color" href="?pagina=<?php echo $pagina_actual + 1; ?>" aria-label="Siguiente">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

    </section>
</body>
<footer class="fixed-bottom">
    <?php include __DIR__ . '/includes/footer.php'; ?>
</footer>
</html>
