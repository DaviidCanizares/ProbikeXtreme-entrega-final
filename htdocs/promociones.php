<?php
// Habilitar reporte de errores (para desarrollo; quitar en producción)
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include __DIR__ . '/includes/conectar_db.php'; // Asegúrate de que la ruta sea correcta

// Consulta para obtener las promociones junto con los datos del producto
$sql_promociones = "SELECT 
                        pr.id, 
                        pr.nombre, 
                        pr.precio, 
                        pr.imagen, 
                        promo.temporada, 
                        promo.descuento,
                        (pr.precio - (pr.precio * promo.descuento / 100)) AS precio_con_descuento
                    FROM promociones promo
                    JOIN products pr ON promo.producto_id = pr.id
                    ORDER BY promo.temporada ASC, pr.nombre ASC";

$stmt = $pdo->prepare($sql_promociones);
$stmt->execute();
$promociones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promociones - ProBikeXtreme</title>
    <?php include __DIR__ . '/includes/enlaces_bootstrap.php'; ?>
</head>
<body>

    <header>
    <?php include __DIR__ . '/includes/header.php'; ?>
    </header>
    <main class="content">
        <div class="container my-5">
            <h2 class="mb-4 text-center text-white">Promociones</h2>
            <div class="row">
                <?php if(!empty($promociones)): ?>
                    <?php foreach($promociones as $promo): ?>
                        <div class="col-md-4 text-white">
                            <div class="promo-card">
                                <?php if(!empty($promo['imagen'])): ?>
                                    <img src="<?php echo htmlspecialchars($promo['imagen']); ?>" alt="<?php echo htmlspecialchars($promo['nombre']); ?>" class="img-fluid promo-img mb-3">
                                <?php endif; ?>
                                <h4><?php echo htmlspecialchars($promo['nombre']); ?></h4>
                                <p>Temporada: <?php echo ucfirst($promo['temporada']); ?></p>
                                <p>
                                    <del>€<?php echo number_format($promo['precio'], 2); ?></del> 
                                    <span class="text-success text-white">€<?php echo number_format($promo['precio_con_descuento'], 2); ?></span>
                                </p>
                                <p¡>Descuento: <?php echo number_format($promo['descuento'], 2); ?>%</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-white">No hay promociones disponibles en este momento.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>    
</body>
<footer class="fixed-bottom">
    <?php include __DIR__ . '/includes/footer.php'; ?>
</footer>
</html>
