<?php 
// Iniciar la sesión es lo primero para acceder a las variables de sesión.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CerroDelivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="/cerrodelivery/index.php">🛵 CerroDelivery</a>
    <div class="ms-auto d-flex align-items-center">
        <?php if (isset($_SESSION['cliente_id'])): // Si el cliente INICIÓ SESIÓN ?>

            <a href="/cerrodelivery/mis_pedidos.php" class="nav-link text-light me-3">Mis Pedidos</a>

            <span class="navbar-text me-3">
                Hola, <?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?>
            </span>
            <a href="/cerrodelivery/procesos/logout_cliente.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>

        <?php else: // Si el cliente NO ha iniciado sesión ?>
            <a href="/cerrodelivery/login_cliente.php" class="btn btn-outline-light btn-sm me-2">Iniciar Sesión</a>
            <a href="/cerrodelivery/registro_cliente.php" class="btn btn-warning btn-sm">Registrarse</a>
        <?php endif; ?>
    </div>
  </div>
</nav>

<main class="container mt-5">

