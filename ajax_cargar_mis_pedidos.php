<?php
session_start();
require_once 'includes/conexion.php';

// Seguridad
if (!isset($_SESSION['cliente_id'])) {
    die();
}
$id_cliente = $_SESSION['cliente_id'];

$sql = "SELECT p.id, p.fecha_pedido, p.monto_total, p.estado_pedido, p.direccion_pedido, r.nombre_restaurante
        FROM pedidos p
        JOIN restaurantes r ON p.id_restaurante = r.id
        WHERE p.id_cliente = ?
        ORDER BY p.fecha_pedido DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$resultado_pedidos = $stmt->get_result();
$contador_pedidos = $resultado_pedidos->num_rows;

// --- Generar el HTML de respuesta con el nuevo diseño ---
if ($resultado_pedidos->num_rows > 0):
    while ($pedido = $resultado_pedidos->fetch_assoc()):
        // Lógica para determinar el progreso para la barra de estado
        $progreso = 0;
        $estado_actual = '';
        $estado_clase = 'info';
        
        switch ($pedido['estado_pedido']) {
            case 'En preparación':
                $progreso = 25;
                $estado_actual = 'Tu pedido se está preparando en la cocina.';
                $estado_clase = 'info';
                break;
            case 'Listo para recoger':
                $progreso = 50;
                $estado_actual = '¡Listo! Esperando a que un repartidor lo recoja.';
                 $estado_clase = 'warning';
                break;
            case 'En camino':
                $progreso = 75;
                $estado_actual = '¡Tu repartidor está en ruta hacia tu ubicación!';
                $estado_clase = 'primary';
                break;
            case 'Entregado':
                $progreso = 100;
                $estado_actual = 'Tu pedido ha sido entregado. ¡Que lo disfrutes!';
                $estado_clase = 'success';
                break;
        }
    ?>
    <div class="card order-card mb-4 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Pedido a <?php echo htmlspecialchars($pedido['nombre_restaurante']); ?></h5>
                <small class="text-muted">Realizado el <?php echo date('d/m/Y \a \l\a\s h:i A', strtotime($pedido['fecha_pedido'])); ?></small>
            </div>
            <div class="text-end">
                <h5 class="mb-0">S/ <?php echo number_format($pedido['monto_total'], 2); ?></h5>
                <small class="text-muted">Total</small>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <h6 class="mb-2">Estado: <span class="text-<?php echo $estado_clase; ?> fw-bold"><?php echo htmlspecialchars($pedido['estado_pedido']); ?></span></h6>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?php echo $estado_clase; ?>" role="progressbar" style="width: <?php echo $progreso; ?>%" aria-valuenow="<?php echo $progreso; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted mt-2 d-block"><?php echo $estado_actual; ?></small>
            </div>
        </div>
        <?php if ($pedido['estado_pedido'] == 'En camino'): ?>
        <div class="card-footer bg-white text-center border-0 pt-0">
            <a href="rastrear_pedido.php?id_pedido=<?php echo $pedido['id']; ?>" class="btn btn-primary btn-track-order">
                <i class="bi bi-geo-alt-fill me-2"></i> Rastrear en el Mapa
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php endwhile;
else: ?>
    <div class="text-center p-5">
        <img src="assets/img/empty-box.svg" alt="Sin pedidos" style="width: 120px;" class="mb-3">
        <h4 class="fw-bold">Aún no tienes pedidos</h4>
        <p class="text-muted">Explora los restaurantes y disfruta de tus platos favoritos.</p>
        <a href="index.php" class="btn btn-primary mt-2">Ver restaurantes</a>
    </div>
<?php endif;

$stmt->close();
$conn->close();
?>