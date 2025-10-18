<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad
if (!isset($_SESSION['repartidor_id'])) {
    die(); 
}
$id_repartidor = $_SESSION['repartidor_id'];

// Consulta que busca pedidos "Listos para recoger" de restaurantes afiliados
// y a los que el repartidor aún no ha postulado.
$sql_pedidos = "SELECT p.id, p.direccion_pedido, r.nombre_restaurante, r.direccion as direccion_restaurante
                FROM pedidos p
                JOIN restaurantes r ON p.id_restaurante = r.id
                JOIN repartidor_afiliaciones af ON p.id_restaurante = af.id_restaurante
                WHERE 
                    p.estado_pedido = 'Listo para recoger' 
                    AND p.id_repartidor IS NULL
                    AND af.id_repartidor = ?
                    AND af.estado_afiliacion = 'aprobado'
                    AND NOT EXISTS (
                        SELECT 1 FROM pedido_solicitudes_entrega pse
                        WHERE pse.id_pedido = p.id AND pse.id_repartidor = ?
                    )";
$stmt_pedidos = $conn->prepare($sql_pedidos);
$stmt_pedidos->bind_param("ii", $id_repartidor, $id_repartidor);
$stmt_pedidos->execute();
$resultado_pedidos = $stmt_pedidos->get_result();

// --- Genera el HTML que se enviará de vuelta ---
if ($resultado_pedidos->num_rows > 0):
    while($pedido = $resultado_pedidos->fetch_assoc()): ?>
    <div class="delivery-card">
        <div class="card-header">Pedido #<?php echo $pedido['id']; ?></div>
        <div class="card-body">
            <div class="route-point pickup"><strong>Recoger en: <?php echo htmlspecialchars($pedido['nombre_restaurante']); ?></strong><small><?php echo htmlspecialchars($pedido['direccion_restaurante']); ?></small></div>
            <div class="route-point dropoff"><strong>Entregar en:</strong><small><?php echo htmlspecialchars($pedido['direccion_pedido']); ?></small></div>
            <form action="../procesos/solicitar_entrega.php" method="POST" class="mt-4">
                <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                <button type="submit" class="btn btn-primary w-100">Quiero llevar este pedido</button>
            </form>
        </div>
    </div>
    <?php endwhile;
else: ?>
    <div class="card dashboard-card">
        <div class="card-body text-center p-5">
            <p class="text-muted">No hay nuevos pedidos para solicitar en este momento.</p>
        </div>
    </div>
<?php endif;

$stmt_pedidos->close();
$conn->close();
?>