<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo repartidores logueados
if (!isset($_SESSION['repartidor_id'])) {
    die(); 
}
$id_repartidor = $_SESSION['repartidor_id'];

// --- CONSULTA CORREGIDA Y SIMPLIFICADA ---
// Ahora se muestran todos los pedidos listos para recoger a los que el repartidor aún no ha postulado.
// Se elimina el requisito de afiliación previa.
$sql_pedidos = "SELECT p.id, p.direccion_pedido, r.nombre_restaurante, r.direccion as direccion_restaurante
                FROM pedidos p
                JOIN restaurantes r ON p.id_restaurante = r.id
                WHERE p.estado_pedido = 'Listo para recoger' AND p.id_repartidor IS NULL
                  AND NOT EXISTS (
                      SELECT 1 FROM pedido_solicitudes_entrega pse
                      WHERE pse.id_pedido = p.id AND pse.id_repartidor = ?
                  )";
$stmt_pedidos = $conn->prepare($sql_pedidos);
// Solo necesitamos vincular el id_repartidor una vez.
$stmt_pedidos->bind_param("i", $id_repartidor);
$stmt_pedidos->execute();
$resultado_pedidos = $stmt_pedidos->get_result();
?>

<div class="row g-4">
<?php if ($resultado_pedidos->num_rows > 0):
    while($pedido = $resultado_pedidos->fetch_assoc()): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card delivery-job-card h-100 shadow-sm">
            <div class="card-header bg-white text-center">
                <h5 class="mb-0 fw-bold">Pedido #<?php echo $pedido['id']; ?></h5>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="route-info flex-grow-1">
                    <div class="route-point pickup">
                        <i class="bi bi-shop icon"></i>
                        <div>
                            <small class="text-muted">RECOGER EN</small>
                            <strong><?php echo htmlspecialchars($pedido['nombre_restaurante']); ?></strong>
                        </div>
                    </div>
                    <div class="route-line"></div>
                    <div class="route-point dropoff">
                        <i class="bi bi-house-door-fill icon"></i>
                        <div>
                            <small class="text-muted">ENTREGAR EN</small>
                            <strong><?php echo htmlspecialchars($pedido['direccion_pedido']); ?></strong>
                        </div>
                    </div>
                </div>
                <form action="../procesos/solicitar_entrega.php" method="POST" class="mt-4">
                    <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                    <button type="submit" class="btn btn-success w-100 fw-bold">¡Quiero llevarlo!</button>
                </form>
            </div>
        </div>
    </div>
    <?php endwhile;
else: ?>
    <div class="col-12">
        <div class="text-center p-5 bg-light rounded-3">
            <img src="../assets/img/no-orders-repartidor.png" alt="Sin pedidos" style="width: 180px;" class="mb-3">
            <h4 class="fw-bold">¡Todo en orden por ahora!</h4>
            <p class="text-muted">Cuando un restaurante tenga un pedido listo, aparecerá aquí.</p>
        </div>
    </div>
<?php endif; ?>
</div>

<?php
$stmt_pedidos->close();
$conn->close();
?>