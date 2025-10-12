<?php
session_start();
// Seguridad
if (!isset($_SESSION['repartidor_id'])) {
    header('Location: ../login_repartidor.php');
    exit();
}
require_once '../includes/conexion.php';

$id_repartidor = $_SESSION['repartidor_id'];

// --- Consulta para ver los pedidos asignados a ESTE repartidor y que están "En camino" ---
$sql = "SELECT p.id, p.direccion_pedido, c.nombre as nombre_cliente, c.telefono as telefono_cliente, r.nombre_restaurante
        FROM pedidos p
        JOIN restaurantes r ON p.id_restaurante = r.id
        JOIN usuarios_clientes c ON p.id_cliente = c.id
        WHERE p.id_repartidor = ? AND p.estado_pedido = 'En camino'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_repartidor);
$stmt->execute();
$resultado_entregas = $stmt->get_result();
?>

<?php include '../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Mis Entregas Activas</h2>
    <a href="dashboard.php" class="btn btn-secondary">Volver a Pedidos Disponibles</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if ($resultado_entregas->num_rows > 0): ?>
            <?php while($entrega = $resultado_entregas->fetch_assoc()): ?>
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <strong>Entregar Pedido #<?php echo $entrega['id']; ?></strong>
                </div>
                <div class="card-body">
                    <p><strong>Restaurante:</strong> <?php echo htmlspecialchars($entrega['nombre_restaurante']); ?></p>
                    <p><strong>Cliente:</strong> <?php echo htmlspecialchars($entrega['nombre_cliente']); ?></p>
                    <p><strong>Teléfono Cliente:</strong> <?php echo htmlspecialchars($entrega['telefono_cliente']); ?></p>
                    <p><strong>Dirección de Entrega:</strong> <?php echo htmlspecialchars($entrega['direccion_pedido']); ?></p>
                    
                    <hr>
                    <p class="text-muted small">Al llegar, muestra el QR del restaurante al cliente para el pago y luego confirma la entrega.</p>
                    
                    <form action="../procesos/completar_entrega.php" method="POST" class="mt-2">
                        <input type="hidden" name="id_pedido" value="<?php echo $entrega['id']; ?>">
                        <button type="submit" class="btn btn-primary w-100">Marcar como Entregado</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted">No tienes entregas activas en este momento.</p>
        <?php endif; ?>
    </div>
</div>

<?php 
$stmt->close();
$conn->close();
include '../includes/footer.php'; 
?>