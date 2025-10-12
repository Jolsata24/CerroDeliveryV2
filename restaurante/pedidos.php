<?php
session_start();
// Seguridad: solo restaurantes logueados pueden ver esta página
if (!isset($_SESSION['restaurante_id'])) {
    header('Location: ../login_restaurante.php');
    exit();
}
require_once '../includes/conexion.php';

$id_restaurante = $_SESSION['restaurante_id'];

// Consulta para obtener los pedidos del restaurante, unidos con los datos del cliente
$sql = "SELECT p.id, p.fecha_pedido, p.monto_total, p.estado_pedido, p.direccion_pedido, c.nombre as nombre_cliente, c.telefono as telefono_cliente
        FROM pedidos p
        JOIN usuarios_clientes c ON p.id_cliente = c.id
        WHERE p.id_restaurante = ?
        ORDER BY p.fecha_pedido DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_restaurante);
$stmt->execute();
$resultado_pedidos = $stmt->get_result();

include '../includes/header.php';
?>

<h1 class="mb-4">Gestión de Pedidos</h1>

<?php while ($pedido = $resultado_pedidos->fetch_assoc()): ?>
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between">
        <strong>Pedido #<?php echo $pedido['id']; ?></strong>
        <span class="badge bg-info"><?php echo htmlspecialchars($pedido['estado_pedido']); ?></span>
    </div>
    <div class="card-body">
        <p><strong>Fecha:</strong> <?php echo date('d/m/Y h:i A', strtotime($pedido['fecha_pedido'])); ?></p>
        <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['nombre_cliente']); ?></p>
        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($pedido['telefono_cliente']); ?></p>
        <p><strong>Dirección de Entrega:</strong> <?php echo htmlspecialchars($pedido['direccion_pedido']); ?></p>
        
        <h5>Detalles del Pedido:</h5>
        <ul>
            <?php
            // Consulta para los detalles de este pedido específico
            $sql_detalle = "SELECT nombre_plato, cantidad, precio_unitario FROM detalle_pedidos WHERE id_pedido = ?";
            $stmt_detalle = $conn->prepare($sql_detalle);
            $stmt_detalle->bind_param("i", $pedido['id']);
            $stmt_detalle->execute();
            $resultado_detalle = $stmt_detalle->get_result();
            while($detalle = $resultado_detalle->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($detalle['cantidad']) . " x " . htmlspecialchars($detalle['nombre_plato']) . " (S/ " . htmlspecialchars($detalle['precio_unitario']) . ")</li>";
            }
            $stmt_detalle->close();
            ?>
        </ul>
        <h4 class="text-end">Total: S/ <?php echo number_format($pedido['monto_total'], 2); ?></h4>
    </div>
    <div class="card-footer text-end">
        <form action="../procesos/actualizar_estado_pedido.php" method="POST" class="d-inline">
            <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
            <select name="nuevo_estado" class="form-select-sm">
                <option value="En preparación">En preparación</option>
                <option value="Listo para recoger">Listo para recoger</option>
                <option value="En camino">En camino</option>
                <option value="Entregado">Entregado</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">Actualizar Estado</button>
        </form>
    </div>
</div>
<?php endwhile; ?>

<?php
$stmt->close();
$conn->close();
include '../includes/footer.php';
?>