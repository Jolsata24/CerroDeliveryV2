<?php
include 'includes/conexion.php';
include 'includes/header.php';

// --- 1. OBTENER TODAS LAS CATEGORÍAS Y DUPLICARLAS PARA EL CARRUSEL ---
$sql_categorias = "SELECT * FROM categorias ORDER BY nombre_categoria ASC";
$resultado_categorias_query = $conn->query($sql_categorias);
$categorias_data = $resultado_categorias_query->fetch_all(MYSQLI_ASSOC);


// --- 2. LÓGICA DE FILTRADO DE RESTAURANTES (Lógica sin cambios) ---
date_default_timezone_set('America/Lima');
$hora_actual = date('H:i:s');
$termino_busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$categoria_seleccionada_id = isset($_GET['categoria_id']) && is_numeric($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : 0;
$nombre_categoria_actual = "Restaurantes Disponibles";

// Se añade r.imagen_fondo a la consulta
$sql = "SELECT DISTINCT r.id, r.nombre_restaurante, r.direccion, r.puntuacion_promedio, r.total_puntuaciones, r.hora_apertura, r.hora_cierre, r.imagen_fondo 
        FROM restaurantes r";
if ($categoria_seleccionada_id > 0) {
    $sql .= " JOIN restaurante_categorias rc ON r.id = rc.id_restaurante";
}
$sql .= " WHERE r.estado = 'activo' AND r.fecha_vencimiento_suscripcion >= CURDATE()";
$params = [];
$types = '';
if ($categoria_seleccionada_id > 0) {
    $sql .= " AND rc.id_categoria = ?";
    $params[] = $categoria_seleccionada_id;
    $types .= 'i';
    $stmt_cat_nombre = $conn->prepare("SELECT nombre_categoria FROM categorias WHERE id = ?");
    $stmt_cat_nombre->bind_param("i", $categoria_seleccionada_id);
    $stmt_cat_nombre->execute();
    $res_cat = $stmt_cat_nombre->get_result();
    if($cat_row = $res_cat->fetch_assoc()) {
        $nombre_categoria_actual = "Restaurantes de " . $cat_row['nombre_categoria'];
    }
    $stmt_cat_nombre->close();
}
if ($termino_busqueda) {
    $sql .= " AND r.nombre_restaurante LIKE ?";
    $params[] = "%" . $termino_busqueda . "%";
    $types .= 's';
}
$sql .= " ORDER BY r.puntuacion_promedio DESC, r.nombre_restaurante ASC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resultado = $stmt->get_result();

// --- 3. MAPEO DE IMÁGENES LOCALES (Lógica sin cambios) ---
$imagenes_locales = [
    'hamburguesas' => 'hamburguesas.png',
    'polloalabrasa' => 'polloalabrasa.png',
    'chaufas' => 'chaufa.png',
    'broaster' => 'broaster.png',
    'salchipapas' => 'salchipapa.png',
    'mariscos' => 'mariscos.png',
];
?>

<div class="hero-section text-center">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-inner">
            <div class="carousel-item active"><img src="assets/img/fondo2.jpg" class="d-block w-100" alt="Fondo de restaurante"></div>
            <div class="carousel-item"><img src="assets/img/fondo1.jpg" class="d-block w-100" alt="Fondo de comida"></div>
            <div class="carousel-item"><img src="assets/img/fondo3.jpg" class="d-block w-100" alt="Fondo de personas comiendo"></div>
        </div>
    </div>
    <div class="container">
        <img src="assets/img/logo.png" alt="CerroDelivery Logo" class="hero-logo mb-3">

        <h1 class="display-4 fw-bold">
            Disfruta de tus platos
            <span id="typed-text-container" class="typed-text-container"></span>
        </h1>
        
        <p class="lead text-muted">Encuentra los mejores restaurantes y recíbelo en minutos.</p>
        
        <div class="row justify-content-center mt-4">
            <div class="col-md-8 col-lg-7">
                <form action="index.php" method="GET" class="d-block d-md-flex gap-2">
                    <input class="form-control form-control-lg mb-2 mb-md-0" type="search" placeholder="Busca tu restaurante preferido..." name="q" value="<?php echo htmlspecialchars($termino_busqueda); ?>">
                    <button class="btn btn-success btn-lg w-100 w-md-auto" type="submit">Buscar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<main class="py-5">
    
    <div class="category-section mb-5">
        <div class="container">
            <h2 class="fw-bold text-center mb-4">¿Qué se te antoja hoy?</h2>
        </div>
        
        <div class="scroller" data-speed="slow">
            <ul class="tag-list scroller__inner">
                <?php
                // Imprimimos la lista de categorías DOS VECES para el efecto infinito
                $categorias_combinadas = array_merge($categorias_data, $categorias_data);
                foreach ($categorias_combinadas as $categoria):
                    $key_imagen = strtolower(str_replace(' ', '', $categoria['nombre_categoria']));
                    $nombre_imagen = $imagenes_locales[$key_imagen] ?? 'default.png';
                ?>
                    <li>
                        <a href="index.php?categoria_id=<?php echo $categoria['id']; ?>" class="category-card-link text-decoration-none">
                            <div class="card category-card-v2 h-100">
                                <img src="assets/img/categorias/<?php echo $nombre_imagen; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($categoria['nombre_categoria']); ?>">
                                <div class="card-body text-center">
                                    <h6 class="card-title fw-bold mb-0"><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></h6>
                                </div>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="container" id="restaurantes-section">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0"><?php echo $nombre_categoria_actual; ?></h2>
            <?php if($categoria_seleccionada_id > 0): ?>
                <a href="index.php" class="btn btn-outline-secondary btn-sm">Ver todos</a>
            <?php endif; ?>
        </div>
        
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
            <?php if ($resultado->num_rows > 0): ?>
                <?php while ($restaurante = $resultado->fetch_assoc()): ?>
                    <?php
                    // Lógica para verificar si está abierto
                    $esta_abierto = false;
                    if (!empty($restaurante['hora_apertura']) && !empty($restaurante['hora_cierre'])) {
                        $apertura = $restaurante['hora_apertura']; $cierre = $restaurante['hora_cierre'];
                        if ($apertura < $cierre) {
                            if ($hora_actual >= $apertura && $hora_actual <= $cierre) $esta_abierto = true;
                        } else {
                            if ($hora_actual >= $apertura || $hora_actual <= $cierre) $esta_abierto = true;
                        }
                    }
                    ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm card-restaurant-bg <?php echo !$esta_abierto ? 'closed' : ''; ?>" style="background-image: url('assets/img/restaurantes/<?php echo htmlspecialchars($restaurante['imagen_fondo']); ?>');">
                             <div class="card-body d-flex flex-column">
                                <div class="mt-auto">
                                    <h5 class="card-title fw-bold"><?php echo htmlspecialchars($restaurante['nombre_restaurante']); ?></h5>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php
                                            $promedio = round($restaurante['puntuacion_promedio'] ?? 0);
                                            for ($i = 1; $i <= 5; $i++) { echo ($i <= $promedio) ? '⭐' : '☆'; }
                                            ?>
                                            <span class="ms-1 small">(<?php echo $restaurante['total_puntuaciones'] ?? 0; ?>)</span>
                                        </div>
                                        <span class="badge <?php echo $esta_abierto ? 'bg-success' : 'bg-danger'; ?>"><?php echo $esta_abierto ? 'Abierto' : 'Cerrado'; ?></span>
                                    </div>
                                    
                                    <?php if ($esta_abierto): ?>
                                        <a href="menu_publico.php?id=<?php echo $restaurante['id']; ?>" class="stretched-link"></a>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12"><div class="alert alert-warning text-center">No se encontraron restaurantes que coincidan con tu búsqueda.</div></div>
            <?php endif; ?>
        </div>
    </div>
    
    <section class="about-section my-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-10 mx-auto text-center text-white">
                    <h1 class="display-4 fw-bold mb-4">Conócenos</h1>
                    <p class="lead mb-5">Somos más que un delivery, somos el sabor de Cerro de Pasco en tu puerta.</p>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="about-card">
                                <h3><i class="bi bi-gem me-2"></i>Nuestra Misión</h3>
                                <p>Facilitar el acceso a la increíble gastronomía de nuestra ciudad, conectando a los comensales con sus restaurantes favoritos a través de una plataforma rápida, segura y confiable, impulsando así el comercio local.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="about-card">
                                <h3><i class="bi bi-bullseye me-2"></i>Nuestra Visión</h3>
                                <p>Ser la aplicación de delivery líder y de mayor confianza en Cerro de Pasco, reconocida por nuestra excelencia en el servicio, nuestro compromiso con la comunidad y por llevar felicidad en cada pedido.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="section-cta">
           <div class="container">
                <div class="text-center mb-5">
                    <h2 class="fw-bold">Únete a <span class="text-primary">CerroDelivery</span></h2>
                    <p class="lead text-muted">Forma parte de nuestra comunidad.</p>
                </div>
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="cta-card text-center">
                            <img src="assets/img/dueño.jpg" class="card-img-top" alt="Dueño de restaurante">
                            <div class="card-body">
                                <h5 class="card-title">Registra tu Restaurante</h5>
                                <p class="card-text text-muted">Aumenta tus ventas llegando a más clientes.</p>
                                <a href="registro_restaurante.php" class="btn btn-cta w-100 mt-3">Registrar mi Negocio</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="cta-card text-center">
                            <img src="assets/img/repartidor.jpg" class="card-img-top" alt="Repartidor">
                            <div class="card-body">
                                <h5 class="card-title">¡Únete como Repartidor!</h5>
                                <p class="card-text text-muted">Gana dinero extra en tu tiempo libre.</p>
                                <a href="registro_repartidor.php" class="btn btn-cta w-100 mt-3">Quiero ser Repartidor</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="cta-card text-center">
                            <img src="assets/img/usuario.jpg" class="card-img-top" alt="Cliente feliz">
                            <div class="card-body">
                                <h5 class="card-title">¿Listo para ordenar?</h5>
                                <p class="card-text text-muted">Crea tu cuenta para una mejor experiencia.</p>
                                <a href="registro_cliente.php" class="btn btn-cta w-100 mt-3">Crear mi Cuenta</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>