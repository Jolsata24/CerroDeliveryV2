<?php
session_start();
require_once 'includes/conexion.php';

// Seguridad
if (!isset($_SESSION['cliente_id'])) {
    die();
}
$id_cliente = $_SESSION['cliente_id'];

// La misma consulta que ya tenías
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

// --- Generar el HTML de respuesta ---
if ($resultado_pedidos->num_rows > 0):
    while ($pedido = $resultado_pedidos->fetch_assoc()): ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <strong>Tu Pedido N° <?php echo $contador_pedidos; ?></strong> 
                <small class="text-muted ms-2">(Ref: #<?php echo $pedido['id']; ?>)</small>
            </div>
            
            <?php
            // Lógica para el color del estado
            $estado_clase = 'bg-secondary';
            switch ($pedido['estado_pedido']) {
                case 'En preparación': $estado_clase = 'bg-info text-dark'; break;
                case 'En camino': $estado_clase = 'bg-primary'; break;
                case 'Entregado': $estado_clase = 'bg-success'; break;
            }
            ?>
            <span class="badge <?php echo $estado_clase; ?>"><?php echo htmlspecialchars($pedido['estado_pedido']); ?></span>
        </div>
        <div class="card-body">
            <p class="mb-1"><strong>Restaurante:</strong> <?php echo htmlspecialchars($pedido['nombre_restaurante']); ?></p>
            <p><strong>Dirección:</strong> <?php echo htmlspecialchars($pedido['direccion_pedido']); ?></p>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
             <h5 class="text-end mb-0">Total: S/ <?php echo number_format($pedido['monto_total'], 2); ?></h5>
            
            <?php if ($pedido['estado_pedido'] == 'En camino'): ?>
                <a href="rastrear_pedido.php?id_pedido=<?php echo $pedido['id']; ?>" class="btn btn-info">
                    🚚 Rastrear Pedido
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php 
        $contador_pedidos--; 
    endwhile;
else: ?>
    <div class="alert alert-info" role="alert">
        Aún no has realizado ningún pedido. ¡<a href="index.php" class="alert-link">Explora los restaurantes</a> y anímate a probar algo!
    </div>
<?php endif;

$stmt->close();
$conn->close();
?>