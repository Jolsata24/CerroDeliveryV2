<?php
session_start();
require_once '../includes/conexion.php';

// Seguridad: solo restaurantes logueados pueden acceder
if (!isset($_SESSION['restaurante_id'])) {
    die(); // Termina la ejecución si no hay sesión
}
$id_restaurante = $_SESSION['restaurante_id'];

// La misma consulta que tenías antes en pedidos.php
$sql_pedidos = "SELECT p.id, p.fecha_pedido, p.monto_total, p.estado_pedido, p.direccion_pedido, c.nombre as nombre_cliente, c.telefono as telefono_cliente
                FROM pedidos p
                JOIN usuarios_clientes c ON p.id_cliente = c.id
                WHERE p.id_restaurante = ?
                ORDER BY p.fecha_pedido DESC";
$stmt_pedidos = $conn->prepare($sql_pedidos);
$stmt_pedidos->bind_param("i", $id_restaurante);
$stmt_pedidos->execute();
$resultado_pedidos = $stmt_pedidos->get_result();

// --- Aquí generamos el HTML que se enviará de vuelta al JavaScript ---
if ($resultado_pedidos->num_rows > 0):
    while ($pedido = $resultado_pedidos->fetch_assoc()): ?>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between">
            <strong>Pedido #<?php echo $pedido['id']; ?></strong>
            <span class="badge bg-info"><?php echo htmlspecialchars($pedido['estado_pedido']); ?></span>
        </div>
        <div class="card-body">
            <p><strong>Fecha:</strong> <?php echo date('d/m/Y h:i A', strtotime($pedido['fecha_pedido'])); ?></p>
            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['nombre_cliente']); ?></p>
            <h4 class="text-end">Total: S/ <?php echo number_format($pedido['monto_total'], 2); ?></h4>
        </div>

        <div class="card-footer">
            <?php if ($pedido['estado_pedido'] == 'Listo para recoger'): ?>
                <h6 class="mb-3">Repartidores que quieren este pedido:</h6>
                
                <?php
                // Sub-consulta para obtener los repartidores que solicitaron ESTE pedido
                $sql_solicitudes = "SELECT pse.id_repartidor, r.nombre 
                                    FROM pedido_solicitudes_entrega pse
                                    JOIN repartidores r ON pse.id_repartidor = r.id
                                    WHERE pse.id_pedido = ? AND pse.estado_solicitud = 'pendiente'";
                $stmt_solicitudes = $conn->prepare($sql_solicitudes);
                $stmt_solicitudes->bind_param("i", $pedido['id']);
                $stmt_solicitudes->execute();
                $resultado_solicitudes = $stmt_solicitudes->get_result();
                ?>
                <?php if ($resultado_solicitudes->num_rows > 0): ?>
                    <ul class="list-group">
                        <?php while($solicitud = $resultado_solicitudes->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($solicitud['nombre']); ?>
                            <form action="../procesos/asignar_repartidor.php" method="POST">
                                <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                                <input type="hidden" name="id_repartidor" value="<?php echo $solicitud['id_repartidor']; ?>">
                                <button type="submit" class="btn btn-success btn-sm">Asignar a este repartidor</button>
                            </form>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">Esperando solicitudes de repartidores...</p>
                <?php endif; ?>
                <?php $stmt_solicitudes->close(); ?>

            <?php else: ?>
                <form action="../procesos/actualizar_estado_pedido.php" method="POST" class="d-flex justify-content-end align-items-center">
                    <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                    <select name="nuevo_estado" class="form-select form-select-sm w-auto me-2">
                         <option value="En preparación">En preparación</option>
                         <option value="Listo para recoger">Listo para recoger</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Actualizar</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile;
else: ?>
    <div class="alert alert-info">No tienes pedidos activos.</div>
<?php endif;

$stmt_pedidos->close();
$conn->close();
?>