<?php
include 'includes/conexion.php';
include 'includes/header.php'; // <-- El <nav> termina y el <main> aún no ha empezado

// La lógica PHP no cambia
date_default_timezone_set('America/Lima');
$hora_actual = date('H:i:s');
$termino_busqueda = '';
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $termino_busqueda = trim($_GET['q']);
}
$sql = "SELECT id, nombre_restaurante, direccion, puntuacion_promedio, total_puntuaciones, hora_apertura, hora_cierre 
        FROM restaurantes 
        WHERE estado = 'activo' AND fecha_vencimiento_suscripcion >= CURDATE()";
if ($termino_busqueda) {
    $sql .= " AND nombre_restaurante LIKE ?";
    $stmt = $conn->prepare($sql);
    $param_busqueda = "%" . $termino_busqueda . "%";
    $stmt->bind_param("s", $param_busqueda);
} else {
    $sql .= " ORDER BY puntuacion_promedio DESC, nombre_restaurante ASC";
    $stmt = $conn->prepare($sql);
}
$stmt->execute();
$resultado = $stmt->get_result();
?>

<div class="hero-section text-center">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="assets/img/fondo1.jpg" class="d-block w-100" alt="...">
            </div>
            <div class="carousel-item">
                <img src="assets/img/fondo2.jpg" class="d-block w-100" alt="...">
            </div>
            <div class="carousel-item">
                <img src="assets/img/fondo3.jpg" class="d-block w-100" alt="...">
            </div>
        </div>
    </div>

    <div class="container">
        <h1 class="display-4 fw-bold">Disfruta de tus platos favoritos</h1>
        <p class="lead text-muted">Encuentra los mejores restaurantes y recíbelo en minutos.</p>
        <div class="row justify-content-center mt-4">
            <div class="col-md-8 col-lg-7">
                <form action="index.php" method="GET" class="d-flex">
                    <input class="form-control form-control-lg me-2" type="search" placeholder="Busca tu restaurante preferido..." name="q" value="<?php echo htmlspecialchars($termino_busqueda); ?>">
                    <button class="btn btn-success btn-lg" type="submit">Buscar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<main class="container py-5">
    
    <h2 class="fw-bold mb-4">Restaurantes Disponibles</h2>
    
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php if ($resultado->num_rows > 0): ?>
            <?php while ($restaurante = $resultado->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm card-restaurant">
                        <div class="card-body d-flex flex-column">
                            <div>
                                <h5 class="card-title"><?php echo htmlspecialchars($restaurante['nombre_restaurante']); ?></h5>
                                <?php
                                $esta_abierto = false;
                                if (!empty($restaurante['hora_apertura']) && !empty($restaurante['hora_cierre'])) {
                                    $apertura = $restaurante['hora_apertura']; $cierre = $restaurante['hora_cierre'];
                                    if ($apertura < $cierre) { if ($hora_actual >= $apertura && $hora_actual <= $cierre) $esta_abierto = true; } 
                                    else { if ($hora_actual >= $apertura || $hora_actual <= $cierre) $esta_abierto = true; }
                                }
                                $estado = $esta_abierto ? 'Abierto' : 'Cerrado'; $clase_estado = $esta_abierto ? 'success' : 'danger';
                                ?>
                                <span class="badge bg-<?php echo $clase_estado; ?> mb-2"><?php echo $estado; ?></span>
                            </div>
                            <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($restaurante['direccion'] ?? 'Dirección no disponible'); ?></small></p>
                            <div>
                                <?php
                                $promedio = round($restaurante['puntuacion_promedio'] ?? 0);
                                for ($i = 1; $i <= 5; $i++) { echo ($i <= $promedio) ? '⭐' : '☆'; }
                                ?>
                                <span class="text-muted ms-1">(<?php echo $restaurante['total_puntuaciones'] ?? 0; ?> reseñas)</span>
                            </div>
                            <div class="mt-auto pt-3">
                                <?php if ($esta_abierto): ?>
                                    <a href="menu_publico.php?id=<?php echo $restaurante['id']; ?>" class="btn btn-primary w-100 stretched-link">Ver Menú</a>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100" disabled>Cerrado ahora</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12"><div class="alert alert-warning text-center">No se encontraron restaurantes con el término "<?php echo htmlspecialchars($termino_busqueda); ?>".</div></div>
        <?php endif; ?>
    </div>

    <div class="section-cta mt-5">
       <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Únete a <span class="text-primary">CerroDelivery</span></h2>
                <p class="lead text-muted">Forma parte de nuestra comunidad.</p>
            </div>
            <div class="row">
                <div class="col-lg-4 mb-4"><div class="cta-card text-center"><img src="assets/img/dueño.jpg" class="card-img-top" alt="Dueño de restaurante"><div class="card-body"><h5 class="card-title">Registra tu Restaurante</h5><p class="card-text text-muted">Aumenta tus ventas llegando a más clientes.</p><a href="registro_restaurante.php" class="btn btn-cta w-100 mt-3">Registrar mi Negocio</a></div></div></div>
                <div class="col-lg-4 mb-4"><div class="cta-card text-center"><img src="assets/img/repartidor.jpg" class="card-img-top" alt="Repartidor"><div class="card-body"><h5 class="card-title">¡Únete como Repartidor!</h5><p class="card-text text-muted">Gana dinero extra en tu tiempo libre.</p><a href="registro_repartidor.php" class="btn btn-cta w-100 mt-3">Quiero ser Repartidor</a></div></div></div>
                <div class="col-lg-4 mb-4"><div class="cta-card text-center"><img src="assets/img/usuario.jpg" class="card-img-top" alt="Cliente feliz"><div class="card-body"><h5 class="card-title">¿Listo para ordenar?</h5><p class="card-text text-muted">Crea tu cuenta para una mejor experiencia.</p><a href="registro_cliente.php" class="btn btn-cta w-100 mt-3">Crear mi Cuenta</a></div></div></div>
            </div>
        </div>
    </div>
</main>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>