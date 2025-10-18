<?php
session_start();
require_once '../includes/conexion.php';
if (!isset($_SESSION['repartidor_id'])) { die(); }
$id_repartidor = $_SESSION['repartidor_id'];

// La misma consulta que tienes en mis_entregas.php
$sql = "SELECT p.id, p.direccion_pedido, p.latitud, p.longitud, c.nombre as nombre_cliente, c.telefono as telefono_cliente, r.nombre_restaurante, r.direccion as direccion_restaurante
        FROM pedidos p
        JOIN restaurantes r ON p.id_restaurante = r.id
        JOIN usuarios_clientes c ON p.id_cliente = c.id
        WHERE p.id_repartidor = ? AND p.estado_pedido = 'En camino'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_repartidor);
$stmt->execute();
$resultado_entregas = $stmt->get_result();
?>

<?php if ($resultado_entregas->num_rows > 0): ?>
    <?php while ($entrega = $resultado_entregas->fetch_assoc()): ?>
    <div class="delivery-card">
        <div class="card-header bg-success text-white">En curso: Pedido #<?php echo $entrega['id']; ?></div>
        <div class="card-body">
            <div class="route-point pickup"><strong>Recogido en: <?php echo htmlspecialchars($entrega['nombre_restaurante']); ?></strong><small><?php echo htmlspecialchars($entrega['direccion_restaurante']); ?></small></div>
            <div class="route-point dropoff"><strong>Entregar a: <?php echo htmlspecialchars($entrega['nombre_cliente']); ?></strong><small><?php echo htmlspecialchars($entrega['direccion_pedido']); ?></small></div>
            <div class="d-grid gap-2 mt-4">
                <a href="https://wa.me/51<?php echo htmlspecialchars($entrega['telefono_cliente']); ?>" target="_blank" class="btn btn-outline-success">Contactar Cliente (WhatsApp)</a>
                <?php if (!empty($entrega['latitud']) && !empty($entrega['longitud'])): ?>
                    <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $entrega['latitud']; ?>,<?php echo $entrega['longitud']; ?>" target="_blank" class="btn btn-outline-primary">📍 Ver en Mapa (GPS)</a>
                <?php endif; ?>
                <form action="../procesos/completar_entrega.php" method="POST" class="mt-2"><input type="hidden" name="id_pedido" value="<?php echo $entrega['id']; ?>"><button type="submit" class="btn btn-primary w-100">Marcar como Entregado</button></form>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="card dashboard-card"><div class="card-body text-center p-5"><p class="text-muted">No tienes entregas activas en este momento.</p></div></div>
<?php endif; ?>