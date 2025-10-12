<?php
session_start();
// El "guardia de seguridad"
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../includes/conexion.php';

// Consulta para RESTAURANTES (esta ya la tenías y está perfecta)
$sql_restaurantes = "SELECT id, nombre_restaurante, email, estado, fecha_vencimiento_suscripcion FROM restaurantes ORDER BY fecha_registro DESC";
$resultado_restaurantes = $conn->query($sql_restaurantes);

// --- NUEVO: CONSULTA PARA REPARTIDORES PENDIENTES ---
// Esta es la parte que faltaba. Busca solo los repartidores pendientes.
$sql_repartidores = "SELECT id, nombre, email, telefono FROM repartidores WHERE estado_aprobacion = 'pendiente' ORDER BY id ASC";
$resultado_repartidores = $conn->query($sql_repartidores);
?>

<?php include '../includes/header.php'; ?>

<h1 class="mb-4">Panel de Administración</h1>

<h3 class="mb-3">Gestionar Restaurantes</h3>
<div class="table-responsive mb-5">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Restaurante</th>
                <th>Email</th>
                <th>Estado</th>
                <th>Vencimiento</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($restaurante = $resultado_restaurantes->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($restaurante['nombre_restaurante']); ?></td>
                    <td><?php echo htmlspecialchars($restaurante['email']); ?></td>
                    <td>
                        <?php if ($restaurante['estado'] == 'activo'): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $restaurante['fecha_vencimiento_suscripcion'] ?? 'Nunca activado'; ?></td>
                    <td>
                        <a href="activar_restaurante.php?id=<?php echo $restaurante['id']; ?>" class="btn btn-primary btn-sm">
                            Activar / Renovar (30 días)
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>


<h3 class="mt-5 mb-3">Aprobar Nuevos Repartidores</h3>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre del Repartidor</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado_repartidores->num_rows > 0): ?>
                        <?php while($repartidor = $resultado_repartidores->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($repartidor['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($repartidor['email']); ?></td>
                                <td><?php echo htmlspecialchars($repartidor['telefono']); ?></td>
                                <td class="text-end">
                                    <a href="aprobar_repartidor.php?id=<?php echo $repartidor['id']; ?>" class="btn btn-success btn-sm">Aprobar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted p-4">No hay solicitudes de repartidores pendientes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>