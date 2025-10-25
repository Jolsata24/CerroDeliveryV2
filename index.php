<?php
// =====================================
// INICIO DE LÓGICA PHP (SIN CAMBIOS)
// =====================================
include 'includes/conexion.php';
include 'includes/header.php';

// --- 1. OBTENER TODAS LAS CATEGORÍAS (Lógica sin cambios) ---
$sql_categorias = "SELECT * FROM categorias ORDER BY nombre_categoria ASC";
$resultado_categorias_query = $conn->query($sql_categorias);
$categorias_data = $resultado_categorias_query->fetch_all(MYSQLI_ASSOC);


// --- 2. LÓGICA DE FILTRADO DE RESTAURANTES (Lógica sin cambios) ---
date_default_timezone_set('America/Lima');
$hora_actual = date('H:i:s');
$termino_busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';
$categoria_seleccionada_id = isset($_GET['categoria_id']) && is_numeric($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : 0;
$nombre_categoria_actual = "Restaurantes Disponibles";

// Se añade r.imagen_fondo a la consulta (Lógica sin cambios)
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
// =====================================
// FIN DE LÓGICA PHP (SIN CAMBIOS)
// =====================================
?>

<div class="hero-section-quickbite">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 hero-text">
                <h1 class="display-3 fw-bold">
                    Disfruta de tus platos favoritos
                </h1>
                <p class="lead text-muted my-4">
                    Encuentra los mejores restaurantes de Cerro de Pasco y recíbelo en minutos.
                </p>
                <a href="#restaurantes-section" class="btn btn-primary btn-order-now btn-lg">
                    ¡Pide ahora!
                </a>
            </div>
            <div class="col-lg-6 hero-image d-none d-lg-block">
                <img src="/cerrodeliveryv2/assets/img/fondo1.jpg" class="img-fluid" alt="Plato de comida">
            </div>
        </div>
    </div>
</div>

<main class="main-content-card">
    <div class="container">
        
        <div class="category-section mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                 <h2 class="fw-bold mb-0">¿Qué se te antoja hoy?</h2>
                 <?php if($categoria_seleccionada_id > 0): // Lógica sin cambios ?>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">Ver todas</a>
                <?php endif; ?>
            </div>
            
            <div class="scroller" data-speed="slow">
                <ul class="tag-list scroller__inner">
                    <?php
                    // Lógica sin cambios
                    $categorias_combinadas = array_merge($categorias_data, $categorias_data);
                    foreach ($categorias_combinadas as $categoria):
                        $key_imagen = strtolower(str_replace(' ', '', $categoria['nombre_categoria']));
                        $nombre_imagen = $imagenes_locales[$key_imagen] ?? 'default.png';
                    ?>
                        <li>
                            <a href="index.php?categoria_id=<?php echo $categoria['id']; ?>" class="category-card-link text-decoration-none">
                                <div class="card category-card-v2 h-100">
                                    <img src="/cerrodeliveryv2/assets/img/categorias/<?php echo $nombre_imagen; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($categoria['nombre_categoria']); ?>">
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
            <h2 class="fw-bold mb-4"><?php echo $nombre_categoria_actual; // Lógica sin cambios ?></h2>
            
            <form action="index.php" method="GET" class="mb-4">
                <div class="input-group input-group-lg">
                    <input class="form-control" type="search" placeholder="Busca tu restaurante preferido..." name="q" value="<?php echo htmlspecialchars($termino_busqueda); // Lógica sin cambios ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                </div>
            </form>
            
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
                <?php if ($resultado->num_rows > 0): // Lógica sin cambios ?>
                    <?php while ($restaurante = $resultado->fetch_assoc()): // Lógica sin cambios ?>
                        <?php
                        // Lógica para verificar si está abierto (sin cambios)
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
                            <div class="card h-100 shadow-sm card-restaurant">
                                <img src="/cerrodeliveryv2/assets/img/restaurantes/<?php echo htmlspecialchars($restaurante['imagen_fondo']); ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?php echo htmlspecialchars($restaurante['nombre_restaurante']); ?>">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($restaurante['nombre_restaurante']); ?></h5>
                                        <span class="badge <?php echo $esta_abierto ? 'bg-success' : 'bg-danger'; ?> ms-2 flex-shrink-0"><?php echo $esta_abierto ? 'Abierto' : 'Cerrado'; ?></span>
                                    </div>
                                    <p class="card-text text-muted small mb-2"><?php echo htmlspecialchars($restaurante['direccion']); ?></p>
                                    <div class="mt-auto">
                                        <?php
                                        // Lógica de estrellas (sin cambios)
                                        $promedio = round($restaurante['puntuacion_promedio'] ?? 0);
                                        for ($i = 1; $i <= 5; $i++) { echo ($i <= $promedio) ? '⭐' : '☆'; }
                                        ?>
                                        <span class="ms-1 small">(<?php echo $restaurante['total_puntuaciones'] ?? 0; ?>)</span>
                                    </div>
                                    
                                    <?php if ($esta_abierto): // Lógica sin cambios ?>
                                        <a href="menu_publico.php?id=<?php echo $restaurante['id']; ?>" class="stretched-link"></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: // Lógica sin cambios ?>
                    <div class="col-12"><div class="alert alert-warning text-center">No se encontraron restaurantes que coincidan con tu búsqueda.</div></div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</main>

<section class="about-section my-5">
    <div class="container">
        </div>
</section>

<div class="container">
    <div class="section-cta">
       <div class="container">
            </div>
    </div>
</div>


<?php
// =====================================
// CIERRE DE PHP (SIN CAMBIOS)
// =====================================
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>