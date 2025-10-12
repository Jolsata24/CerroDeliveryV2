<?php
session_start(); // <-- ESTA LÍNEA ES LA NUEVA Y MÁS IMPORTANTE
include 'includes/conexion.php';

// 1. Validar que recibimos un ID por la URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Petición inválida.");
}
$id_restaurante = $_GET['id'];

// 2. Obtener la información del restaurante
$sql_restaurante = "SELECT * FROM restaurantes WHERE id = ? AND estado = 'activo' LIMIT 1";
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

            <?php if (isset($_SESSION['cliente_id'])): ?>
                <a href="/cerrodelivery/checkout.php" class="btn btn-success mt-3">Ver Carrito y Finalizar Pedido</a>
            <?php endif; ?>
        </div>
    </div>

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