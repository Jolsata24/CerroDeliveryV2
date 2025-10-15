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

    <link rel="stylesheet" href="/cerrodelivery/assets/css/style.css">

</head>

<body class="bg-light">
    <div id="preloader">
    <img src="/cerrodelivery/assets/img/loader.gif" alt="Cargando..." class="preloader-logo">
</div>
    <script>
        <?php if (!isset($_SESSION['cliente_id'])): ?>
            sessionStorage.removeItem('carritoData');
        <?php endif; ?>
    </script>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm fixed-top"></nav>
    <script>
        // Si la sesión de PHP para el cliente no existe...
        <?php if (!isset($_SESSION['cliente_id'])): ?>
            // ...entonces borramos el carrito del navegador.
            sessionStorage.removeItem('carritoData');
        <?php endif; ?>
    </script>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/cerrodelivery/index.php">
                <img src="/cerrodelivery/assets/img/logo.png" alt="CerroDelivery Logo" style="height: 40px;">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <?php if (isset($_SESSION['cliente_id'])): // Si el cliente INICIÓ SESIÓN 
                    ?>
                        <li class="nav-item">
                            <a href="/cerrodelivery/mis_pedidos.php" class="nav-link">Mis Pedidos</a>
                        </li>
                        <li class="nav-item">
                            <span class="navbar-text me-3">
                                Hola, <?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a href="/cerrodelivery/procesos/logout_cliente.php" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
                        </li>
                    <?php else: // Si el cliente NO ha iniciado sesión 
                    ?>
                        <li class="nav-item">
                            <a href="/cerrodelivery/login_cliente.php" class="btn btn-outline-primary btn-sm me-lg-2 mb-2 mb-lg-0">Iniciar Sesión</a>
                        </li>
                        <li class="nav-item">
                            <a href="/cerrodelivery/registro_cliente.php" class="btn btn-primary btn-sm">Registrarse</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-4">