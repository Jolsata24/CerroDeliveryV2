<?php
session_start();
// Seguridad: solo repartidores logueados pueden acceder
if (!isset($_SESSION['repartidor_id'])) {
    header('Location: ../login_repartidor.php');
    exit();
}
require_once '../includes/conexion.php';

$id_repartidor = $_SESSION['repartidor_id'];

// --- Consulta para ver los pedidos que están LISTOS y no tienen repartidor asignado ---
$sql = "SELECT p.id, p.direccion_pedido, r.nombre_restaurante, r.direccion as direccion_restaurante
        FROM pedidos p
        JOIN restaurantes r ON p.id_restaurante = r.id
        WHERE p.estado_pedido = 'Listo para recoger' AND p.id_repartidor IS NULL
        ORDER BY p.fecha_pedido ASC";
$resultado_pedidos = $conn->query($sql);
?>

<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Panel de Repartidor</h2>
        <p class="text-muted">Hola, <?php echo htmlspecialchars($_SESSION['repartidor_nombre']); ?>. Aquí están los pedidos disponibles.</p>
        <a href="mis_entregas.php" class="btn btn-info">Ver Mis Entregas Activas</a>
    </div>
    <a href="../procesos/logout_repartidor.php" class="btn btn-danger">Cerrar Sesión</a>
    <?php // Nota: El logout_repartidor.php es idéntico al logout_cliente.php, solo redirige a login_repartidor.php ?>
</div>


<div class="card">
    <div class="card-header">
        <h3>Pedidos Listos para Recoger</h3>
    </div>
    <div class="card-body">
        <?php if ($resultado_pedidos->num_rows > 0): ?>
            <div class="list-group">
                <?php while($pedido = $resultado_pedidos->fetch_assoc()): ?>
                <div class="list-group-item list-group-item-action flex-column align-items-start mb-3 border rounded">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">Pedido #<?php echo $pedido['id']; ?></h5>
                    </div>
                    <p class="mb-1">
                        <strong>Recoger en:</strong> <?php echo htmlspecialchars($pedido['nombre_restaurante']); ?><br>
                        <small class="text-muted"><?php echo htmlspecialchars($pedido['direccion_restaurante']); ?></small>
                    </p>
                    <p class="mb-1">
                        <strong>Entregar a:</strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($pedido['direccion_pedido']); ?></small>
                    </p>
                    <form action="../procesos/aceptar_pedido.php" method="POST" class="mt-3">
                        <input type="hidden" name="id_pedido" value="<?php echo $pedido['id']; ?>">
                        <button type="submit" class="btn btn-success">Aceptar y Ver Detalles</button>
                    </form>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">No hay pedidos listos para recoger en este momento. ¡Mantente atento!</p>
        <?php endif; ?>
    </div>
</div>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>