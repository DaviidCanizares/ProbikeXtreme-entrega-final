<?php
// Ejecutamos la consulta para obtener sólo las categorías activas
try {
    $sql = "SELECT * FROM categories WHERE estado = 'activo'";
    $stmt = $pdo->query($sql);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener las categorías: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProBikeXtreme</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>
<body>
    <!-- Left Section -->
    <aside>
        <h3 class="text-white">Categorías</h3>
        <ul class="nav flex-column">
            <?php
            foreach ($categories as $category) {
                // Solo mostramos las categorías principales (donde parent_id es null)
                if ($category['parent_id'] === null) {
                    echo "<li class='nav-item'>";
                    echo "<a class='nav-link text-white' data-bs-toggle='collapse' href='#collapse{$category['id']}' role='button' aria-expanded='false' aria-controls='collapse{$category['id']}'>";
                    echo htmlspecialchars($category['nombre']);
                    echo "</a>";

                    // Buscamos las subcategorías de esta categoría
                    echo "<div class='collapse' id='collapse{$category['id']}'>";
                    echo "<ul>";
                    foreach ($categories as $subcategory) {
                        if ($subcategory['parent_id'] === $category['id']) {
                            // Redirige a productos.php con el ID de la subcategoría
                            echo "<li><a href='/products/productos.php?categoria_id=" . htmlspecialchars($subcategory['id']) . "' class='text-white'>" . htmlspecialchars($subcategory['nombre']) . "</a></li>";
                        }
                    }
                    echo "</ul>";
                    echo "</div>";
                    echo "</li>";
                }
            }
            ?>
        </ul>
    </aside>
</body>
</html>
