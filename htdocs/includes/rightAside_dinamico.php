<?php
// Iniciar sesión
session_start();

// Verificamos si el usuario está logueado y es cliente
if (isset($_SESSION['role']) && $_SESSION['role'] === 'cliente') {
    // Mostrar panel de cliente
    ?>
    <h3 class="text-white">Bienvenido <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Cliente'); ?></h3>
<ul class="nav flex-column">
    <li class="nav-item">
        <a href="/cliente/mis_datos.php" class="nav-link text-white">Mis Datos</a>
    </li>
    <li class="nav-item">
        <a href="/cliente/mis_pedidos.php" class="nav-link text-white">Mis Pedidos</a>
    </li>
    <li class="nav-item">
        <a href="/administrador/devoluciones.php" class="nav-link text-white">Devoluciones</a>
    </li>
</ul>
<form action="/auth/logout.php" method="post">
    <button type="submit" class="btn btn-second-color w-100">Cerrar Sesión</button>
</form>
    <?php
} else {
    // Mostramos formulario de inicio de sesión para visitantes
    ?>
    <h3 class="text-white">Iniciar sesión</h3>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <form action="/index.php" method="post" class="text-white p-3 rounded">
        <div class="mb-3">
            <label for="nombre" class="form-label">Usuario:</label>
            <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Usuario" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña:</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Contraseña" required>
        </div>
        <button type="submit" name="login" class="btn btn-formulario w-100">Iniciar sesión</button>
        <p class="quita-subrayado pt-3">
            <a href="/auth/recuperar_contrasena.php" class="me-3 text-white">Olvidé mi contraseña</a>
            <a href="/auth/register.php" class="text-white">Regístrate</a>
        </p>
    </form>
    <?php
}
?>
