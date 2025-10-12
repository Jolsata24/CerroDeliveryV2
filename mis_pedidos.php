<?php
session_start();
// Seguridad: solo clientes logueados pueden ver esta página
if (!isset($_SESSION['cliente_id'])) {
    header('Location: login_cliente.php');
    exit();
}
require_once 'includes/conexion.php';

$id_cliente = $_SESSION['cliente_id'];

// Consulta para obtener los pedidos de este cliente, unidos con los datos del restaurante
$sql = "SELECT p.id, p.fecha_pedido, p.monto_total, p.estado_pedido, p.direccion_pedido, r.nombre_restaurante
        FROM pedidos p
        JOIN restaurantes r ON p.id_restaurante = r.id
        WHERE p.id_cliente = ?
        ORDER BY p.fecha_pedido DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$resultado_pedidos = $stmt->get_result();

include 'includes/header.php';
?>

<h1 class="mb-4">Mi Historial de Pedidos</h1>

<?php if ($resultado_pedidos->num_rows > 0): ?>
    <?php while ($pedido = $resultado_pedidos->fetch_assoc()): ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <strong>Pedido #<?php echo $pedido['id']; ?></strong>
                    <small class="text-muted ms-2">Realizado el: <?php echo date('d/m/Y h:i A', strtotime($pedido['fecha_pedido'])); ?></small>
                </div>

                <?php
                // Lógica para cambiar el color del estado
                $estado_clase = 'bg-secondary'; // Gris por defecto
                switch ($pedido['estado_pedido']) {
                    case 'En preparación':
                        $estado_clase = 'bg-info text-dark';
                        break;
                    case 'En camino':
                        $estado_clase = 'bg-primary';
                        break;
                    case 'Entregado':
                        $estado_clase = 'bg-success';
                        break;
                }
                ?>
                <span class="badge <?php echo $estado_clase; ?>"><?php echo htmlspecialchars($pedido['estado_pedido']); ?></span>
            </div>
            <div class="card-body">
                <p><strong>Restaurante:</strong> <?php echo htmlspecialchars($pedido['nombre_restaurante']); ?></p>
                <p><strong>Dirección de Entrega:</strong> <?php echo htmlspecialchars($pedido['direccion_pedido']); ?></p>

                <h6>Detalles del Pedido:</h6>
                <ul class="list-group list-group-flush">
                    <?php
                    // Consulta para los detalles de este pedido específico
                    $sql_detalle = "SELECT nombre_plato, cantidad, precio_unitario FROM detalle_pedidos WHERE id_pedido = ?";
                    $stmt_detalle = $conn->prepare($sql_detalle);
                    $stmt_detalle->bind_param("i", $pedido['id']);
                    $stmt_detalle->execute();
                    $resultado_detalle = $stmt_detalle->get_result();
                    while ($detalle = $resultado_detalle->fetch_assoc()) {
                        echo "<li class='list-group-item'>" . htmlspecialchars($detalle['cantidad']) . " x " . htmlspecialchars($detalle['nombre_plato']) . "</li>";
                    }
                    $stmt_detalle->close();
                    ?>
                </ul>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <h5 class="text-end mb-0">Total Pagado: S/ <?php echo number_format($pedido['monto_total'], 2); ?></h5>

                <?php if ($pedido['estado_pedido'] == 'En camino'): ?>
                    <a href="rastrear_pedido.php?id_pedido=<?php echo $pedido['id']; ?>" class="btn btn-info">
                        🚚 Rastrear Pedido
                    </a>
                <?php endif; ?>
            </div>

        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        Aún no has realizado ningún pedido. ¡<a href="index.php" class="alert-link">Explora los restaurantes</a> y anímate a probar algo!
    </div>
<?php endif; ?>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>