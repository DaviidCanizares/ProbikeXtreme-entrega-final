<?php
// Iniciar sesión
session_start();

// Conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Obtener el ID de la categoría desde la URL
$categoria_id = isset($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : 0;

// Obtener el término de búsqueda desde la URL
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// Inicializar variable de productos
$productos = [];

// Consultar productos según la categoría o el término de búsqueda
try {
    if (!empty($query)) {
        // Búsqueda por nombre con promoción
        $sql = "SELECT p.*, 
                       promo.descuento, 
                       (p.precio - (p.precio * IFNULL(promo.descuento, 0) / 100)) AS precio_con_descuento 
                FROM products p 
                LEFT JOIN promociones promo ON p.id = promo.producto_id 
                WHERE p.nombre LIKE :query AND p.estado = 'activo'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':query' => '%' . $query . '%']);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($categoria_id > 0) {
        // Filtrar productos por categoría con promoción
        $sql = "SELECT p.*, 
                       promo.descuento, 
                       (p.precio - (p.precio * IFNULL(promo.descuento, 0) / 100)) AS precio_con_descuento 
                FROM products p 
                LEFT JOIN promociones promo ON p.id = promo.producto_id 
                WHERE p.categoria_id = :categoria_id AND p.estado = 'activo'";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Mostrar todos los productos con promoción
        $sql = "SELECT p.*, 
                       promo.descuento, 
                       (p.precio - (p.precio * IFNULL(promo.descuento, 0) / 100)) AS precio_con_descuento 
                FROM products p 
                LEFT JOIN promociones promo ON p.id = promo.producto_id 
                WHERE p.estado = 'activo'";
        $stmt = $pdo->query($sql);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Error al consultar los productos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - ProBikeXtreme</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
    <!-- El bloque <style> ha sido eliminado -->
</head>
<body>
    <!-- Header -->
    <header>
        <?php include __DIR__ . '/../includes/header.php'; ?>
    </header>

    <!-- Main content -->
    <div class="content container-fluid">
        <div class="row" style="height: 100vh;">
            <!-- Left Section -->
            <aside class="col-md-2 p-3" style="overflow-y: auto;">
                <?php include __DIR__ . '/../includes/leftAside_dinamico.php'; ?>
            </aside>

            <!-- Central Section -->
            <section class="col-md-8 d-flex flex-column align-items-center" style="overflow-y: auto; height: 100vh;">
                <?php if (!empty($productos)): ?>
                    <div class="row w-100">
                        <!-- Tarjetas de producto -->
                        <?php foreach ($productos as $producto): ?>
                            <div class="col-md-6 col-lg-3 mb-4 d-flex">
                                <div class="card d-flex flex-column">
                                    <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" 
                                         class="card-img-top" 
                                         alt="Imagen de <?php echo htmlspecialchars($producto['nombre']); ?>">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                                        <?php if (!empty($producto['descuento']) && $producto['descuento'] > 0): ?>
                                            <p class="card-text">
                                                <del>€<?php echo number_format($producto['precio'], 2); ?></del>
                                                <br>
                                                <span class="text-success">€<?php echo number_format($producto['precio_con_descuento'], 2); ?></span>
                                                <br>
                                                <small>(Descuento: <?php echo number_format($producto['descuento'], 2); ?>%)</small>
                                            </p>
                                        <?php else: ?>
                                            <p class="card-text"><strong>€<?php echo number_format($producto['precio'], 2); ?></strong></p>
                                        <?php endif; ?>
                                        <div class="mt-auto">
                                            <!-- Formulario para añadir al carrito -->
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
                    <p class="text-muted text-center">No se encontraron productos.</p>
                <?php endif; ?>
            </section>

            <!-- Right Section -->
            <aside class="col-md-2 p-3" style="overflow-y: auto;">
                <?php include __DIR__ . '/../includes/rightAside_dinamico.php'; ?>
            </aside>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white p-3">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
