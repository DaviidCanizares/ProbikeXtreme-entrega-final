<?php
session_start();

// Verificamos si el usuario es administrador o empleado
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['administrador', 'empleado'])) {
    header('Location: ../index.php');
    exit();
}

// Incluir conexión a la base de datos
include __DIR__ . '/../includes/conectar_db.php';

// Variables para filtros 
$filtro = "";

// Creamos paginación
$pedidos_por_pagina = 5; 
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if ($pagina_actual < 1) {
    $pagina_actual = 1;
}
$offset = ($pagina_actual - 1) * $pedidos_por_pagina;

// Primero, contar el total de pedidos.
try {
    $sql_total = "SELECT COUNT(*) AS total FROM pedidos p " . $filtro;
    $stmt_total = $pdo->prepare($sql_total);
    $stmt_total->execute();
    $total_pedidos = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    die("Error al contar los pedidos: " . $e->getMessage());
}

// Calculamos el total de páginas
$total_paginas = ceil($total_pedidos / $pedidos_por_pagina);

// Obtenemos los pedidos con paginación
try {
    $sql = "SELECT 
                p.id AS pedido_id, 
                p.usuario_id, 
                u.nombre AS cliente_nombre, 
                u.email, 
                p.fecha_pedido, 
                p.total, 
                p.estado
            FROM pedidos p
            JOIN users u ON p.usuario_id = u.id
            " . $filtro . "
            ORDER BY p.fecha_pedido DESC
            LIMIT :offset, :limit";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $pedidos_por_pagina, PDO::PARAM_INT);
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar los datos: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - Administrador</title>
    <?php include __DIR__ . '/../includes/enlaces_bootstrap.php'; ?>
</head>
<body>
    <!-- Header -->
    <header>
        <?php include __DIR__ . '/../includes/header_admin.php'; ?>
    </header>

    <!-- Contenido principal -->
    <div class="container mt-5 flex-grow-1">
        <h2 class="mb-4">Gestión de Pedidos</h2>

        <?php if (!empty($pedidos)): ?>
            <form action="actualizar_estado_pedido.php" method="POST">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Pedido</th>
                            <th>Cliente</th>
                            <th>Email</th>
                            <th>Fecha</th>
                            <th>Total (€)</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $pedido): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pedido['pedido_id']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['email']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['fecha_pedido']); ?></td>
                                <td>€<?php echo number_format($pedido['total'], 2); ?></td>
                                <td>
                                    <select class="form-select form-select-sm" name="estado[<?php echo $pedido['pedido_id']; ?>]">
                                        <option value="pendiente" <?php if ($pedido['estado'] === 'pendiente') echo 'selected'; ?>>Pendiente</option>
                                        <option value="procesado" <?php if ($pedido['estado'] === 'procesado') echo 'selected'; ?>>Procesado</option>
                                        <option value="enviado" <?php if ($pedido['estado'] === 'enviado') echo 'selected'; ?>>Enviado</option>
                                        <option value="cancelado" <?php if ($pedido['estado'] === 'cancelado') echo 'selected'; ?>>Cancelado</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

               <?php if ($total_paginas > 1): ?>
  <nav aria-label="Paginación de pedidos">
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
<?php endif; ?>


                <!-- Botón para actualizar los estados -->
                <div class="col-md-12 d-flex justify-content-between align-items-center mt-4 mb-4">
                    <?php if ($_SESSION['role'] === 'administrador'): ?>
                        <a href="../administrador/index_administrador.php" class="btn btn-secondary">Volver al Panel de Administrador</a>
                    <?php elseif ($_SESSION['role'] === 'empleado'): ?>
                        <a href="../empleado/index_empleado.php" class="btn btn-secondary">Volver al Panel de Empleado</a>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-second-color">Actualizar pedidos</button>
                </div>
            </form>
        <?php else: ?>
            <p class="text-center">No hay pedidos registrados en este momento.</p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        <?php include __DIR__ . '/../includes/footer.php'; ?>
    </footer>
</body>
</html>
