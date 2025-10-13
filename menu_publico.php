<?php
session_start(); // <-- ESTA LÍNEA ES LA NUEVA Y MÁS IMPORTANTE
include 'includes/conexion.php';

// 1. Validar que recibimos un ID por la URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Petición inválida.");
}
$id_restaurante = $_GET['id'];

// 2. Obtener la información del restaurante
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

// 3. Obtener los platos de ESE restaurante
$sql_platos = "SELECT * FROM menu_platos WHERE id_restaurante = ?";
$stmt_platos = $conn->prepare($sql_platos);
$stmt_platos->bind_param("i", $id_restaurante);
$stmt_platos->execute();
$resultado_platos = $stmt_platos->get_result();

include 'includes/header.php';
?>

<div class="container">
    <div class="p-5 mb-4 bg-light rounded-3">
        <div class="container-fluid py-5">
            <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($restaurante['nombre_restaurante']); ?></h1>
            <p class="col-md-8 fs-4"><?php echo htmlspecialchars($restaurante['direccion'] ?? ''); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($restaurante['telefono'] ?? 'No especificado'); ?></p>

            <div class="mt-3">
                <?php
                $promedio = round($restaurante['puntuacion_promedio'] ?? 0);
                $total_votos = $restaurante['total_puntuaciones'] ?? 0;
                for ($i = 1; $i <= 5; $i++) {
                    echo ($i <= $promedio) ? '⭐' : '☆';
                }
                ?>
                <span class="text-muted ms-1">(<?php echo $total_votos; ?> reseñas)</span>
            </div>

            <?php if (isset($_SESSION['cliente_id'])): ?>
                <button type="button" class="btn btn-warning mt-3" data-bs-toggle="modal" data-bs-target="#ratingModal">
                    ⭐ Califícanos
                </button>
            <?php else: ?>
                <p class="mt-2"><a href="login_cliente.php">Inicia sesión</a> para poder calificar este restaurante.</p>
            <?php endif; ?>

            <?php if (isset($_SESSION['cliente_id'])): ?>
                <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="ratingModalLabel">Califica a <?php echo htmlspecialchars($restaurante['nombre_restaurante']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center rating-modal" data-restaurante-id="<?php echo $restaurante['id']; ?>">
                                <p>Tu opinión es importante para nosotros.</p>

                                <div class="rating-stars">
                                    <input type="radio" id="star5-modal" name="rating-modal" value="5" /><label for="star5-modal"></label>
                                    <input type="radio" id="star4-modal" name="rating-modal" value="4" /><label for="star4-modal"></label>
                                    <input type="radio" id="star3-modal" name="rating-modal" value="3" /><label for="star3-modal"></label>
                                    <input type="radio" id="star2-modal" name="rating-modal" value="2" /><label for="star2-modal"></label>
                                    <input type="radio" id="star1-modal" name="rating-modal" value="1" /><label for="star1-modal"></label>
                                </div>

                                <div class="mt-3 rating-feedback"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="confirmRatingBtn" disabled>Confirmar Voto</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            

            <?php if (isset($_SESSION['cliente_id'])): ?>
                <a href="/cerrodelivery/checkout.php" class="btn btn-success mt-3">Ver Carrito y Finalizar Pedido</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_SESSION['cliente_id'])): ?>
        <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ratingModalLabel">Califica a <?php echo htmlspecialchars($restaurante['nombre_restaurante']); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p>Selecciona tu puntuación:</p>
                        <div class="rating-container" data-restaurante-id="<?php echo $restaurante['id']; ?>">
                            <div class="rating">
                                <input type="radio" id="star5-modal" name="rating-modal" value="5" /><label for="star5-modal"></label>
                                <input type="radio" id="star4-modal" name="rating-modal" value="4" /><label for="star4-modal"></label>
                                <input type="radio" id="star3-modal" name="rating-modal" value="3" /><label for="star3-modal"></label>
                                <input type="radio" id="star2-modal" name="rating-modal" value="2" /><label for="star2-modal"></label>
                                <input type="radio" id="star1-modal" name="rating-modal" value="1" /><label for="star1-modal"></label>
                            </div>
                            <div class="mt-3 rating-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="mb-4">Nuestro Menú</h2>
    <div class="row">
        <?php if ($resultado_platos->num_rows > 0): ?>
            <?php while ($plato = $resultado_platos->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <img src="assets/img/platos/<?php echo htmlspecialchars($plato['foto_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($plato['nombre_plato']); ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($plato['nombre_plato']); ?></h5>
                            <p class="card-text text-muted flex-grow-1"><?php echo htmlspecialchars($plato['descripcion']); ?></p>
                            <p class="h4 text-end text-success">S/ <?php echo htmlspecialchars($plato['precio']); ?></p>

                            <?php if (isset($_SESSION['cliente_id'])): ?>
                                <a href="#" class="btn btn-warning mt-auto add-to-cart-btn"
                                    data-id="<?php echo $plato['id']; ?>"
                                    data-nombre="<?php echo htmlspecialchars($plato['nombre_plato']); ?>"
                                    data-precio="<?php echo $plato['precio']; ?>"
                                    data-restaurante-id="<?php echo $id_restaurante; // <-- LÍNEA NUEVA 
                                                            ?>">
                                    Añadir al Carrito
                                </a>
                            <?php else: ?>
                                <a href="/cerrodelivery/login_cliente.php" class="btn btn-secondary mt-auto">
                                    Inicia Sesión para Añadir
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Este restaurante aún no ha publicado su menú.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$stmt_restaurante->close();
$stmt_platos->close();
$conn->close();
include 'includes/footer.php';
?>