<?php
// Incluimos la conexión y la cabecera
include 'includes/conexion.php';
include 'includes/header.php';

// Consulta para mostrar solo los restaurantes activos
$sql = "SELECT * FROM restaurantes WHERE estado = 'activo' AND fecha_vencimiento_suscripcion >= CURDATE() ORDER BY nombre_restaurante ASC";
$resultado = $conn->query($sql);
?>

<div class="container py-4">

    <div class="p-3 mb-4 bg-light border rounded-3 text-center">
        <h5 class="text-muted">Accesos Directos (Temporal)</h5>
        <div class="btn-group" role="group" aria-label="Botones de acceso">
            <a href="login_cliente.php" class="btn btn-primary">Login Cliente</a>
            <a href="login_restaurante.php" class="btn btn-success">Login Restaurante</a>

            <a href="login_repartidor.php" class="btn btn-info">Login Repartidor</a>
            <a href="admin/login.php" class="btn btn-danger">Login Admin</a>
        </div>
    </div>
    <div class="text-center mb-5">
        <h1>Encuentra tu comida favorita</h1>
        <p class="lead text-muted">Explora los mejores restaurantes locales y pide directamente.</p>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

        <?php if ($resultado->num_rows > 0): ?>
            <?php while ($restaurante = $resultado->fetch_assoc()): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($restaurante['nombre_restaurante']); ?></h5>
                            <p class="card-text">
                                <small class="text-muted"><?php echo htmlspecialchars($restaurante['direccion'] ?? 'Dirección no disponible'); ?></small>
                            </p>
                            <a href="menu_publico.php?id=<?php echo $restaurante['id']; ?>" class="btn btn-primary stretched-link">Ver Menú</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="text-center text-muted">😢 Lo sentimos, no hay restaurantes disponibles en este momento.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>