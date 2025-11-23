<?php
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

    <link rel="icon" type="image/png" href="assets/img/logoheader.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="assets/css/style.css">

    <?php
    $script_name = basename($_SERVER['SCRIPT_NAME']);
    if ($script_name == 'index.php'): ?>
        <link rel="stylesheet" href="assets/css/index.css">
    <?php elseif ($script_name == 'menu_publico.php'): ?>
        <link rel="stylesheet" href="assets/css/menu_publico.css">
    <?php elseif ($script_name == 'checkout.php'): ?>
        <link rel="stylesheet" href="assets/css/checkout.css">
    <?php elseif ($script_name == 'mis_pedidos.php'): ?>
        <link rel="stylesheet" href="assets/css/mis_pedidos.css">
    <?php elseif ($script_name == 'login_cliente.php'): ?>
        <link rel="stylesheet" href="assets/css/login.css">
    <?php elseif ($script_name == 'login_repartidor.php'): ?>
        <link rel="stylesheet" href="assets/css/login.css">
    <?php elseif ($script_name == 'login_restaurante.php'): ?>
        <link rel="stylesheet" href="assets/css/login.css">
    <?php endif; ?>
    

    <script>
        const CLIENTE_ID = <?php echo isset($_SESSION['cliente_id']) ? json_encode($_SESSION['cliente_id']) : 'null'; ?>;
    </script>
</head>

<body>
    <div id="preloader">
        <img src="assets/img/loader.gif" alt="Cargando..." style="width: 60px;">
    </div>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/img/logo.png" alt="CerroDelivery" style="height: 45px; filter: brightness(0) invert(1);">
            </a>

            <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link text-white" href="index.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="index.php#restaurantes-section">Restaurantes</a></li>
                    <?php if (isset($_SESSION['cliente_id'])): ?>
                        <li class="nav-item"><a class="nav-link text-white" href="mis_pedidos.php">Mis Pedidos</a></li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav align-items-lg-center gap-2">
                    <?php if (isset($_SESSION['cliente_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white fw-bold" href="#" role="button" data-bs-toggle="dropdown">
                                Hola, <?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="mis_pedidos.php">Mis Pedidos</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="procesos/logout_cliente.php">Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="login_cliente.php" class="btn btn-outline-light btn-sm px-4 rounded-pill">Ingresar</a>
                        </li>
                        <li class="nav-item">
                            <a href="registro_cliente.php" class="btn btn-light text-primary btn-sm px-4 rounded-pill fw-bold">Registrarse</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (basename($_SERVER['PHP_SELF']) != 'index.php'): ?>
        <main class="container mt-4">
    <?php endif; ?>