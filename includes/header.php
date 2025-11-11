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

    <?php
    // Obtiene el nombre del archivo actual (ej: "index.php", "mis_pedidos.php")
    $script_name = basename($_SERVER['SCRIPT_NAME']);
    $script_path = $_SERVER['SCRIPT_NAME'];

    // Carga un CSS específico solo para la página de inicio
    if ($script_name == 'index.php'):
    ?>
        <link rel="stylesheet" href="/cerrodeliveryv2/assets/css/index.css?v=2">
    <?php
    // Carga un CSS específico solo para la página de menú público
    elseif ($script_name == 'menu_publico.php'):
    ?>
        <link rel="stylesheet" href="/cerrodeliveryv2/assets/css/menu_publico.css?v=1">
    <?php
    // Carga un CSS específico solo para la página de checkout
    elseif ($script_name == 'checkout.php'):
    ?>
        <link rel="stylesheet" href="/cerrodeliveryv2/assets/css/checkout.css?v=1">
    
    <?php
    // Carga un CSS específico solo para la página de "Mis Pedidos"
    elseif ($script_name == 'mis_pedidos.php'):
    ?>
        <link rel="stylesheet" href="/cerrodeliveryv2/assets/css/mis_pedidos.css?v=1">
    
    <?php
    // Carga un CSS específico solo para el dashboard del restaurante
    elseif ($script_name == 'dashboard.php' && strpos($script_path, '/restaurante/') !== false):
    ?>
        <link rel="stylesheet" href="/cerrodeliveryv2/assets/css/restaurante-dashboard.css?v=1">
    
    <?php
    // Carga un CSS específico solo para el dashboard del repartidor
    elseif ($script_name == 'dashboard.php' && strpos($script_path, '/repartidor/') !== false):
    ?>
        <link rel="stylesheet" href="/cerrodeliveryv2/assets/css/repartidor-dashboard.css?v=1">
    
    <?php
    // === INICIO DE LA MODIFICACIÓN ===
    // Carga un CSS específico para "Mis Entregas" del repartidor
    elseif ($script_name == 'mis_entregas.php' && strpos($script_path, '/repartidor/') !== false):
    ?>
        <link rel="stylesheet" href="/cerrodeliveryv2/assets/css/repartidor-entregas.css?v=1">
    <?php endif; ?>
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
            <a class="navbar-brand" href="/cerrodeliveryv2/index.php">
                <img src="/cerrodeliveryv2/assets/img/logo.png" alt="CerroDelivery Logo" style="height: 40px;">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/cerrodeliveryv2/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/cerrodeliveryv2/index.php#restaurantes-section">Restaurantes</a>
                    </li>
                    <?php if (isset($_SESSION['cliente_id'])): // Lógica de sesión original 
                    ?>
                        <li class="nav-item">
                            <a href="/cerrodeliveryv2/mis_pedidos.php" class="nav-link">Mis Pedidos</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav align-items-lg-center">
                    <?php if (isset($_SESSION['cliente_id'])): // Lógica de sesión original 
                    ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Hola, <?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="/cerrodeliveryv2/mis_pedidos.php">Mis Pedidos</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="/cerrodeliveryv2/procesos/logout_cliente.php">Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    <?php else: // Lógica de sesión original 
                    ?>
                        <li class="nav-item">
                            <a href="/cerrodeliveryv2/login_cliente.php" class="btn btn-outline-secondary btn-sm me-lg-2 mb-2 mb-lg-0">Iniciar Sesión</a>
                        </li>
                        <li class="nav-item">
                            <a href="/cerrodeliveryv2/registro_cliente.php" class="btn btn-primary btn-sm">Registrarse</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container mt-4">