<?php
// 1. INICIAR SESIÓN Y SEGURIDAD (Sin cambios)
session_start();
if (!isset($_SESSION['restaurante_id'])) {
    header('Location: ../login_restaurante.php');
    exit();
}

// 2. CONEXIÓN A LA BASE DE DATOS Y CONSULTAS (Sin cambios)
require_once '../includes/conexion.php';
$id_restaurante_actual = $_SESSION['restaurante_id'];

// --- CONSULTA para obtener los datos del restaurante (Sin cambios)
$sql_restaurante = "SELECT hora_apertura, hora_cierre, telefono FROM restaurantes WHERE id = ?";
$stmt_restaurante = $conn->prepare($sql_restaurante);
$stmt_restaurante->bind_param("i", $id_restaurante_actual);
$stmt_restaurante->execute();
$restaurante_data = $stmt_restaurante->get_result()->fetch_assoc();
$stmt_restaurante->close();

// --- Consulta para obtener los platos (Sin cambios)
$sql_platos = "SELECT * FROM menu_platos WHERE id_restaurante = ? ORDER BY id DESC";
$stmt_platos = $conn->prepare($sql_platos);
$stmt_platos->bind_param("i", $id_restaurante_actual);
$stmt_platos->execute();
$resultado_platos = $stmt_platos->get_result();

// --- Consulta para contar los pedidos pendientes (Sin cambios)
$sql_count = "SELECT COUNT(id) AS total_pendientes FROM pedidos WHERE id_restaurante = ? AND (estado_pedido = 'Pendiente' OR estado_pedido = 'En preparación')";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $id_restaurante_actual);
$stmt_count->execute();
$row_count = $stmt_count->get_result()->fetch_assoc();
$total_pendientes = $row_count['total_pendientes'];

// --- NUEVA CONSULTA: SOLICITUDES DE AFILIACIÓN PENDIENTES ---
$sql_solicitudes = "SELECT af.id as id_afiliacion, rep.nombre as nombre_repartidor, rep.telefono
                    FROM repartidor_afiliaciones af
                    JOIN repartidores rep ON af.id_repartidor = rep.id
                    WHERE af.id_restaurante = ? AND af.estado_afiliacion = 'pendiente'";
$stmt_solicitudes = $conn->prepare($sql_solicitudes);
$stmt_solicitudes->bind_param("i", $id_restaurante_actual);
$stmt_solicitudes->execute();
$resultado_solicitudes = $stmt_solicitudes->get_result();
// 3. INCLUIR LA CABECERA HTML (Sin cambios)
include '../includes/header.php';
?>

<div class="dashboard-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="h3">Panel de <?php echo htmlspecialchars($_SESSION['restaurante_nombre']); ?></h2>
        <p class="text-muted mb-0">Gestiona tu negocio en CerroDelivery.</p>
    </div>
    <a href="logout.php" class="btn btn-outline-danger">Cerrar Sesión</a>
</div>

<div class="row mb-4 g-4">
    <div class="col-md-6">
        <div class="card summary-card h-100">
            <div class="card-body">
                <h5 class="card-title">Pedidos Activos</h5>
                <p class="display-4 fw-bold"><?php echo $total_pendientes; ?></p>
                <a href="pedidos.php" class="btn btn-primary stretched-link">Ver Pedidos</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card summary-card h-100">
            <div class="card-body">
                <h5 class="card-title">Mi Menú</h5>
                <p class="display-4 fw-bold"><?php echo $resultado_platos->num_rows; ?></p>
                <p>platos registrados</p>
                </div>
        </div>
    </div>
</div>

<div class="card dashboard-card mb-4">
    <div class="card-header">Configuración General</div>
    <div class="card-body">
        <form action="../procesos/actualizar_horario.php" method="POST" class="mb-4">
            <h6>Horario Comercial</h6>
            <div class="row align-items-end">
                <div class="col-md-5"><label for="hora_apertura" class="form-label">Apertura</label><input type="time" class="form-control" name="hora_apertura" value="<?php echo htmlspecialchars($restaurante_data['hora_apertura'] ?? ''); ?>"></div>
                <div class="col-md-5"><label for="hora_cierre" class="form-label">Cierre</label><input type="time" class="form-control" name="hora_cierre" value="<?php echo htmlspecialchars($restaurante_data['hora_cierre'] ?? ''); ?>"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-secondary w-100">Guardar</button></div>
            </div>
        </form>
        <hr>
        <form action="../procesos/actualizar_telefono.php" method="POST" class="mt-4">
             <h6>Notificaciones por WhatsApp</h6>
            <div class="row align-items-end">
                <div class="col-md-10">
                    <label for="telefono" class="form-label">Número de WhatsApp</label>
                    <div class="input-group">
                        <span class="input-group-text">+51</span>
                        <input type="tel" class="form-control" name="telefono" placeholder="987654321" value="<?php echo htmlspecialchars($restaurante_data['telefono'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col-md-2"><button type="submit" class="btn btn-secondary w-100">Guardar</button></div>
            </div>
        </form>
    </div>
</div>

<div class="card dashboard-card mb-4">
    <div class="card-header">Añadir Nuevo Plato</div>
    <div class="card-body">
        <form action="../procesos/procesar_agregar_plato.php" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3"><label for="nombre_plato" class="form-label">Nombre del Plato</label><input type="text" class="form-control" name="nombre_plato" required></div>
                <div class="col-md-6 mb-3"><label for="precio" class="form-label">Precio (S/)</label><input type="number" step="0.10" class="form-control" name="precio" required></div>
            </div>
            <div class="mb-3"><label for="descripcion" class="form-label">Descripción</label><textarea class="form-control" name="descripcion" rows="2"></textarea></div>
            <div class="mb-3"><label for="foto" class="form-label">Foto del Plato (Opcional)</label><input class="form-control" type="file" name="foto"></div>
            <button type="submit" class="btn btn-primary">Añadir Plato al Menú</button>
        </form>
    </div>
</div>

<div class="card dashboard-card">
    <div class="card-header">Tu Menú Actual</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Foto</th><th>Nombre</th><th>Descripción</th><th>Precio</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado_platos->num_rows > 0): ?>
                        <?php while ($plato = $resultado_platos->fetch_assoc()): ?>
                            <tr>
                                <td><img src="../assets/img/platos/<?php echo htmlspecialchars($plato['foto_url']); ?>" alt="<?php echo htmlspecialchars($plato['nombre_plato']); ?>" width="80" class="img-thumbnail"></td>
                                <td><?php echo htmlspecialchars($plato['nombre_plato']); ?></td>
                                <td class="text-muted"><?php echo htmlspecialchars($plato['descripcion']); ?></td>
                                <td class="fw-bold">S/ <?php echo number_format($plato['precio'], 2); ?></td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-secondary">Editar</a>
                                    <a href="#" class="btn btn-sm btn-outline-danger">Eliminar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center p-4 text-muted">Aún no has añadido ningún plato. ¡Usa el formulario de arriba para empezar!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card dashboard-card mt-4">
    <div class="card-header">
        Solicitudes de Afiliación de Repartidores
        <?php if ($resultado_solicitudes->num_rows > 0): ?>
            <span class="badge bg-warning text-dark ms-2"><?php echo $resultado_solicitudes->num_rows; ?> pendiente(s)</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
         <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nombre del Repartidor</th>
                        <th>Teléfono</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado_solicitudes->num_rows > 0): ?>
                        <?php while($solicitud = $resultado_solicitudes->fetch_assoc()): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($solicitud['nombre_repartidor']); ?></td>
                                <td><?php echo htmlspecialchars($solicitud['telefono']); ?></td>
                                <td class="text-end">
                                    <form action="../procesos/gestionar_afiliacion.php" method="POST" class="d-inline">
                                        <input type="hidden" name="id_afiliacion" value="<?php echo $solicitud['id_afiliacion']; ?>">
                                        <button type="submit" name="accion" value="aprobar" class="btn btn-success btn-sm">Aprobar</button>
                                        <button type="submit" name="accion" value="rechazar" class="btn btn-danger btn-sm">Rechazar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center p-4 text-muted">No tienes solicitudes de afiliación pendientes.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
// 7. CERRAR CONEXIONES Y FOOTER (Sin cambios)
$stmt_platos->close();
$stmt_count->close();
$conn->close();
include '../includes/footer.php';
?>