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
    

    <link rel="icon" type="image/png" href="/cerrodeliveryv2/assets/img/logoheader.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/cerrodeliveryv2/assets/css/style.css"> 
    
    <script>
        // Esta lógica de JS se mantiene
        const CLIENTE_ID = <?php echo isset($_SESSION['cliente_id']) ? json_encode($_SESSION['cliente_id']) : 'null'; ?>;
    </script>
</head>

<body class="bg-light">

    <div id="preloader">
        <img src="/cerrodeliveryv2/assets/img/loader.gif" alt="Cargando..." class="preloader-logo">
    </div>

    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/index.php">
                <img src="/cerrodeliveryv2/assets/img/logo.png" alt="CerroDelivery Logo" style="height: 40px;">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#restaurantes-section">Restaurantes</a>
                    </li>
                     <?php if (isset($_SESSION['cliente_id'])): // Lógica de sesión original ?>
                         <li class="nav-item">
                            <a href="mis_pedidos.php" class="nav-link">Mis Pedidos</a>
                        </li>
                     <?php endif; ?>
                </ul>

                <ul class="navbar-nav align-items-lg-center">
                    <?php if (isset($_SESSION['cliente_id'])): // Lógica de sesión original ?>
                        <li class="nav-item dropdown">
                             <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Hola, <?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="mis_pedidos.php">Mis Pedidos</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/procesos/logout_cliente.php">Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    <?php else: // Lógica de sesión original ?>
                        <li class="nav-item">
                            <a href="/login_cliente.php" class="btn btn-outline-secondary btn-sm me-lg-2 mb-2 mb-lg-0">Iniciar Sesión</a>
                        </li>
                        <li class="nav-item">
                            <a href="/registro_cliente.php" class="btn btn-primary btn-sm">Registrarse</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-4">