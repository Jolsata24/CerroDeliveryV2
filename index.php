<?php
include 'includes/conexion.php';
include 'includes/header.php';

// Establecer la zona horaria para obtener la hora correcta
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
    $sql .= " ORDER BY nombre_restaurante ASC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$resultado = $stmt->get_result();
?>

<div class="container py-4">
    <div class="text-center mb-5">
        <h1>Encuentra tu comida favorita</h1>
        <p class="lead text-muted">Explora los mejores restaurantes locales y pide directamente.</p>
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <form action="index.php" method="GET" class="d-flex">
                    <input class="form-control me-2" type="search" placeholder="Buscar por nombre de restaurante..." name="q" value="<?php echo htmlspecialchars($termino_busqueda); ?>">
                    <button class="btn btn-outline-success" type="submit">Buscar</button>
                </form>
            </div>
        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php if ($resultado->num_rows > 0): ?>
            <?php while ($restaurante = $resultado->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div>
                                <h5 class="card-title"><?php echo htmlspecialchars($restaurante['nombre_restaurante']); ?></h5>
                                <?php
                                $estado = 'Cerrado';
                                $esta_abierto = false;
                                if (!empty($restaurante['hora_apertura']) && !empty($restaurante['hora_cierre'])) {
                                    $apertura = $restaurante['hora_apertura'];
                                    $cierre = $restaurante['hora_cierre'];
                                    if ($apertura < $cierre) {
                                        if ($hora_actual >= $apertura && $hora_actual <= $cierre) {
                                            $esta_abierto = true;
                                        }
                                    } else {
                                        if ($hora_actual >= $apertura || $hora_actual <= $cierre) {
                                            $esta_abierto = true;
                                        }
                                    }
                                }
                                if ($esta_abierto) {
                                    $estado = 'Abierto';
                                    $clase_estado = 'success';
                                } else {
                                    $estado = 'Cerrado';
                                    $clase_estado = 'danger';
                                }
                                ?>
                                <span class="badge bg-<?php echo $clase_estado; ?> mb-2"><?php echo $estado; ?></span>
                            </div>
                            
                            <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($restaurante['direccion'] ?? 'Dirección no disponible'); ?></small></p>
                            
                            <?php if (!empty($restaurante['hora_apertura']) && !empty($restaurante['hora_cierre'])): ?>
                                <p class="card-text"><small>Horario: <?php echo date("g:i A", strtotime($restaurante['hora_apertura'])); ?> - <?php echo date("g:i A", strtotime($restaurante['hora_cierre'])); ?></small></p>
                            <?php endif; ?>
                            
                            <div>
                                <?php
                                $promedio = round($restaurante['puntuacion_promedio']);
                                for ($i = 1; $i <= 5; $i++) { echo ($i <= $promedio) ? '⭐' : '☆'; }
                                ?>
                                <span class="text-muted ms-1">(<?php echo $restaurante['total_puntuaciones']; ?> reseñas)</span>
                            </div>
                            
                            <div class="mt-auto">
                                <?php if ($esta_abierto): ?>
                                    <a href="menu_publico.php?id=<?php echo $restaurante['id']; ?>" class="btn btn-primary stretched-link">Ver Menú</a>
                                <?php else: ?>
                                    <button class="btn btn-secondary stretched-link" disabled>Cerrado ahora</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <?php endif; ?>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>