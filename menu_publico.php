<?php
session_start();
include 'includes/conexion.php';

// 1. Validaciones y consultas PHP (sin cambios)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Petición inválida.");
}
$id_restaurante = $_GET['id'];

// Obtener la información del restaurante
$sql_restaurante = "SELECT id, nombre_restaurante, direccion, telefono, puntuacion_promedio, total_puntuaciones 
                    FROM restaurantes 
                    WHERE id = ? AND estado = 'activo' LIMIT 1";
$stmt_restaurante = $conn->prepare($sql_restaurante);
$stmt_restaurante->bind_param("i", $id_restaurante);
$stmt_restaurante->execute();
$resultado_restaurante = $stmt_restaurante->get_result();

if ($resultado_restaurante->num_rows == 0) {
    die("Restaurante no encontrado o no disponible.");
}
$restaurante = $resultado_restaurante->fetch_assoc();

// Obtener los platos del restaurante
$sql_platos = "SELECT * FROM menu_platos WHERE id_restaurante = ? ORDER BY nombre_plato ASC";
$stmt_platos = $conn->prepare($sql_platos);
$stmt_platos->bind_param("i", $id_restaurante);
$stmt_platos->execute();
$resultado_platos = $stmt_platos->get_result();

include 'includes/header.php'; // Se incluye el header normalmente
?>

<div class="menu-hero-banner" style="background-image: url('assets/img/fondo3.jpg');">
    <div class="restaurant-header">
        <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($restaurante['nombre_restaurante']); ?></h1>
        <div class="d-flex align-items-center justify-content-center mb-3">
            <?php
            $promedio = round($restaurante['puntuacion_promedio'] ?? 0);
            $total_votos = $restaurante['total_puntuaciones'] ?? 0;
            for ($i = 1; $i <= 5; $i++) { echo ($i <= $promedio) ? '⭐' : '☆'; }
            ?>
            <span class="ms-2 text-muted">(<?php echo $total_votos; ?> reseñas)</span>
        </div>
        <p class="fs-5 text-muted"><?php echo htmlspecialchars($restaurante['direccion'] ?? ''); ?></p>
    </div>
</div>

<div class="container py-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Nuestro Menú</h2>
        <?php if (isset($_SESSION['cliente_id'])): ?>
            <a href="checkout.php" class="btn btn-success">
                🛒 Ver Carrito
            </a>
        <?php endif; ?>
    </div>

    <div class="row">
        <?php if ($resultado_platos->num_rows > 0): ?>
            <?php while ($plato = $resultado_platos->fetch_assoc()): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card-plato">
                        <div class="img-container">
                             <img src="assets/img/platos/<?php echo htmlspecialchars($plato['foto_url']); ?>" alt="<?php echo htmlspecialchars($plato['nombre_plato']); ?>">
                        </div>
                        <div class="card-body">
                            <div>
                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($plato['nombre_plato']); ?></h5>
                                <p class="card-text text-muted small"><?php echo htmlspecialchars($plato['descripcion']); ?></p>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <p class="price mb-0">S/ <?php echo number_format($plato['precio'], 2); ?></p>
                                
                                <?php if (isset($_SESSION['cliente_id'])): ?>
                                    <button class="btn btn-warning add-to-cart-btn"
                                        data-id="<?php echo $plato['id']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($plato['nombre_plato']); ?>"
                                        data-precio="<?php echo $plato['precio']; ?>"
                                        data-restaurante-id="<?php echo $id_restaurante; ?>">
                                        Añadir
                                    </button>
                                <?php else: ?>
                                    <a href="login_cliente.php" class="btn btn-secondary">
                                        Inicia Sesión
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <p class="alert alert-info">Este restaurante aún no ha publicado su menú.</p>
            </div>
        <?php endif; ?>
    </div>
</div>


<?php if (isset($_SESSION['cliente_id'])): ?>
<div class="modal fade" id="ratingModal" tabindex="-1">
    </div>
<?php endif; ?>

<?php
$stmt_restaurante->close();
$stmt_platos->close();
$conn->close();
include 'includes/footer.php';
?>