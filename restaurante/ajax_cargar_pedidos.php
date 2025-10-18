<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['restaurante_id'])) {
    die();
}
$id_restaurante = $_SESSION['restaurante_id'];

$sql_pedidos = "SELECT p.id, p.fecha_pedido, p.monto_total, p.estado_pedido, p.direccion_pedido, c.nombre as nombre_cliente, c.telefono as telefono_cliente
                FROM pedidos p
                JOIN usuarios_clientes c ON p.id_cliente = c.id
                WHERE p.id_restaurante = ?
                ORDER BY p.fecha_pedido DESC";
$stmt_pedidos = $conn->prepare($sql_pedidos);
$stmt_pedidos->bind_param("i", $id_restaurante);
$stmt_pedidos->execute();
$resultado_pedidos = $stmt_pedidos->get_result();

if ($resultado_pedidos->num_rows > 0):
    while ($pedido = $resultado_pedidos->fetch_assoc()):
        // Lógica para los colores y textos de estado
        $estado_clase_borde = 'border-info';
        $estado_clase_texto = 'text-info';
        $icono_estado = 'bi-stopwatch';

        switch ($pedido['estado_pedido']) {
            case 'En preparación':
                $estado_clase_borde = 'border-warning';
                $estado_clase_texto = 'text-warning';
                $icono_estado = 'bi-egg-fried';
                break;
            case 'Listo para recoger':
                $estado_clase_borde = 'border-primary';
                $estado_clase_texto = 'text-primary';
                $icono_estado = 'bi-bag-check-fill';
                break;
            case 'En camino':
                $estado_clase_borde = 'border-success';
                $estado_clase_texto = 'text-success';
                $icono_estado = 'bi-truck';
                break;
            case 'Entregado':
                 $estado_clase_borde = 'border-secondary';
                 $estado_clase_texto = 'text-secondary';
                 $icono_estado = 'bi-check2-circle';
                 break;
        }
    ?>
    <div class="card pedido-card shadow-sm mb-4 <?php echo $estado_clase_borde; ?>">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h5 class="fw-bold mb-1">Pedido #<?php echo $pedido['id']; ?></h5>
                    <p class="mb-1"><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['nombre_cliente']); ?></p>
                    <p class="text-muted mb-0"><small><?php echo date('d/m/Y h:i A', strtotime($pedido['fecha_pedido'])); ?></small></p>
                </div>

                <div class="col-md-4 text-center my-3 my-md-0">
                    <h6 class="text-uppercase small">Estado</h6>
                    <div class="d-flex align-items-center justify-content-center <?php echo $estado_clase_texto; ?>">
                        <i class="bi <?php echo $icono_estado; ?> fs-4 me-2"></i>
                        <span class="fw-bold fs-5"><?php echo htmlspecialchars($pedido['estado_pedido']); ?></span>
                    </div>
                </div>

                <div class="col-md-4 text-md-end">
                    <h6 class="text-uppercase small">Total</h6>
                    <p class="fw-bold fs-4 mb-0">S/ <?php echo number_format($pedido['monto_total'], 2); ?></p>
                </div>
            </div>
        </div>

        <div class="card-footer bg-light">
            <?php if ($pedido['estado_pedido'] == 'Listo para recoger'): ?>
                <h6 class="small fw-bold mb-2 text-center">REPARTIDORES POSTULANDO:</h6>
                <div class="solicitudes-container" data-id-pedido="<?php echo $pedido['id']; ?>">
                   </div>
            <?php elseif ($pedido['estado_pedido'] != 'Entregado'): ?>
                <form action="../procesos/actualizar_estado_pedido.php" method="POST" class="d-flex justify-content-end align-items-center">
                    <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                    <label class="form-label me-2 mb-0 small">Cambiar a:</label>
                    <select name="nuevo_estado" class="form-select form-select-sm w-auto me-2">
                        <option value="En preparación" <?php echo ($pedido['estado_pedido'] == 'En preparación') ? 'selected' : ''; ?>>En preparación</option>
                        <option value="Listo para recoger" <?php echo ($pedido['estado_pedido'] == 'Listo para recoger') ? 'selected' : ''; ?>>Listo para recoger</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Actualizar</button>
                </form>
            <?php else: ?>
                <p class="text-muted text-center mb-0 small">Este pedido ya fue completado.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile;
else: ?>
    <div class="text-center p-5">
        <img src="../assets/img/empty-orders.svg" alt="Sin pedidos" style="width: 150px;" class="mb-3">
        <h4 class="fw-bold">No tienes pedidos activos</h4>
        <p class="text-muted">Cuando un cliente realice una compra, aparecerá aquí.</p>
    </div>
<?php endif;

$stmt_pedidos->close();
$conn->close();
?>