<?php
session_start();
// Seguridad (sin cambios)
if (!isset($_SESSION['repartidor_id'])) {
    header('Location: ../login_repartidor.php');
    exit();
}
require_once '../includes/conexion.php';
$id_repartidor = $_SESSION['repartidor_id'];

// --- Consulta (sin cambios) ---
$sql = "SELECT p.id, p.direccion_pedido, r.nombre_restaurante, r.direccion as direccion_restaurante
        FROM pedidos p
        JOIN restaurantes r ON p.id_restaurante = r.id
        WHERE p.estado_pedido = 'Listo para recoger' AND p.id_repartidor IS NULL
        ORDER BY p.fecha_pedido ASC";
$resultado_pedidos = $conn->query($sql);

include '../includes/header.php';
?>

<div class="dashboard-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="h3">Pedidos Disponibles</h2>
        <p class="text-muted mb-0">Hola, <?php echo htmlspecialchars($_SESSION['repartidor_nombre']); ?>. Acepta un pedido para empezar.</p>
    </div>
    <div>
        <a href="mis_entregas.php" class="btn btn-info text-white me-2">Ver Mis Entregas</a>
        <a href="../procesos/logout_repartidor.php" class="btn btn-outline-danger">Cerrar Sesión</a>
    </div>
</div>

<?php if ($resultado_pedidos->num_rows > 0): ?>
    <?php while($pedido = $resultado_pedidos->fetch_assoc()): ?>
    <div class="delivery-card">
        <div class="card-header">
            Pedido #<?php echo $pedido['id']; ?>
        </div>
        <div class="card-body">
            <div class="route-point pickup">
                <strong>Recoger en: <?php echo htmlspecialchars($pedido['nombre_restaurante']); ?></strong>
                <small><?php echo htmlspecialchars($pedido['direccion_restaurante']); ?></small>
            </div>
            <div class="route-point dropoff">
                <strong>Entregar en:</strong>
                <small><?php echo htmlspecialchars($pedido['direccion_pedido']); ?></small>
            </div>
            <form action="../procesos/aceptar_pedido.php" method="POST" class="mt-4">
                <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                <button type="submit" class="btn btn-success w-100">Aceptar Pedido</button>
            </form>
        </div>
    </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="card dashboard-card">
        <div class="card-body text-center p-5">
            <p class="text-muted">No hay pedidos listos para recoger en este momento. ¡Mantente atento!</p>
        </div>
    </div>
<?php endif; ?>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>