<?php
// --- Lógica PHP (Modificada) ---
session_start();
if (!isset($_SESSION['restaurante_id'])) {
    header('Location: ../login_restaurante.php');
    exit();
}
require_once '../includes/conexion.php';
$id_restaurante_actual = $_SESSION['restaurante_id'];

// Consultas para datos del restaurante y platos (SIN CAMBIOS)
$sql_restaurante = "SELECT hora_apertura, hora_cierre, telefono FROM restaurantes WHERE id = ?";
$stmt_restaurante = $conn->prepare($sql_restaurante);
$stmt_restaurante->bind_param("i", $id_restaurante_actual);
$stmt_restaurante->execute();
$restaurante_data = $stmt_restaurante->get_result()->fetch_assoc();
$stmt_restaurante->close();

$sql_platos = "SELECT * FROM menu_platos WHERE id_restaurante = ? ORDER BY id DESC";
$stmt_platos = $conn->prepare($sql_platos);
$stmt_platos->bind_param("i", $id_restaurante_actual);
$stmt_platos->execute();
$resultado_platos = $stmt_platos->get_result();

// Consulta para pedidos pendientes (SIN CAMBIOS)
$sql_count = "SELECT COUNT(id) AS total_pendientes FROM pedidos WHERE id_restaurante = ? AND (estado_pedido = 'Pendiente' OR estado_pedido = 'En preparación')";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $id_restaurante_actual);
$stmt_count->execute();
$row_count = $stmt_count->get_result()->fetch_assoc();
$total_pendientes = $row_count['total_pendientes'];

include '../includes/header.php';
?>

<div class="hero-quickbite">
    <div class="container hero-text">
        <div class="dashboard-header d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 fw-bold">Panel de <?php echo htmlspecialchars($_SESSION['restaurante_nombre']); ?></h1>
                <p class="lead text-white-50 mb-0">Un resumen de la actividad de tu negocio.</p>
            </div>
            <a href="logout.php" class="btn btn-outline-danger mt-2 mt-md-0"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a>
        </div>
    </div>
</div>

<div class="main-content-overlay">
<div class="container">

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card summary-card-gradient summary-card-1 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-white">Pedidos Activos</h5>
                        <p class="display-4 fw-bold text-white mb-0"><?php echo $total_pendientes; ?></p>
                        <a href="pedidos.php" class="stretched-link text-white-50">Gestionar pedidos</a>
                    </div>
                    <div class="icon-circle">
                        <i class="bi bi-receipt-cutoff"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card summary-card-gradient summary-card-2 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title text-white">Platos en Menú</h5>
                        <p class="display-4 fw-bold text-white mb-0"><?php echo $resultado_platos->num_rows; ?></p>
                        <span class="text-white-50">Total registrados</span>
                    </div>
                    <div class="icon-circle">
                        <i class="bi bi-card-checklist"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card dashboard-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Añadir Nuevo Plato al Menú</h5>
                </div>
                <div class="card-body">
                    <form action="../procesos/procesar_agregar_plato.php" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label for="nombre_plato" class="form-label">Nombre del Plato</label><input type="text" class="form-control" name="nombre_plato" required></div>
                            <div class="col-md-6 mb-3"><label for="precio" class="form-label">Precio (S/)</label><input type="number" step="0.10" class="form-control" name="precio" required></div>
                        </div>
                        <div class="mb-3"><label for="descripcion" class="form-label">Descripción</label><textarea class="form-control" name="descripcion" rows="2"></textarea></div>
                        <div class="mb-3"><label for="foto" class="form-label">Foto del Plato</label><input class="form-control" type="file" name="foto"></div>
                        <button type="submit" class="btn btn-primary w-100">Añadir Plato</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card dashboard-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Configuración General</h5>
                </div>
                <div class="card-body">
                    <form action="../procesos/actualizar_horario.php" method="POST" class="mb-4">
                        <h6><i class="bi bi-clock-fill me-2"></i>Horario Comercial</h6>
                        <div class="row align-items-end g-2">
                            <div class="col"><label class="form-label">Apertura</label><input type="time" class="form-control" name="hora_apertura" value="<?php echo htmlspecialchars($restaurante_data['hora_apertura'] ?? ''); ?>"></div>
                            <div class="col"><label class="form-label">Cierre</label><input type="time" class="form-control" name="hora_cierre" value="<?php echo htmlspecialchars($restaurante_data['hora_cierre'] ?? ''); ?>"></div>
                            <div class="col-auto"><button type="submit" class="btn btn-secondary w-100">Guardar</button></div>
                        </div>
                    </form>
                    <hr>
                    <form action="../procesos/actualizar_telefono.php" method="POST" class="mt-4">
                        <h6><i class="bi bi-whatsapp me-2"></i>Notificaciones</h6>
                        <div class="row align-items-end g-2">
                            <div class="col">
                                <label class="form-label">Número de WhatsApp</label>
                                <div class="input-group"><span class="input-group-text">+51</span><input type="tel" class="form-control" name="telefono" value="<?php echo htmlspecialchars($restaurante_data['telefono'] ?? ''); ?>" required></div>
                            </div>
                            <div class="col-auto"><button type="submit" class="btn btn-secondary w-100">Guardar</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4 mb-4">
        <div class="col-lg-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">Imagen de Portada de tu Restaurante</h5>
                </div>
                <div class="card-body">
                    <form action="../procesos/actualizar_imagen_restaurante.php" method="POST" enctype="multipart/form-data">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <p class="text-muted">Esta imagen aparecerá como fondo en la página principal. Sube una foto atractiva de tu local.</p>
                                <div class="mb-3">
                                    <label for="foto_restaurante" class="form-label">Seleccionar nueva imagen:</label>
                                    <input class="form-control" type="file" name="foto_restaurante" id="foto_restaurante" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Subir y Guardar Imagen</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">Tu Menú Actual</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Foto</th>
                                    <th>Plato</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Precio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultado_platos->num_rows > 0): ?>
                                    <?php 
                                    // Aseguramos que el puntero del resultado esté al inicio
                                    $resultado_platos->data_seek(0); 
                                    while($plato = $resultado_platos->fetch_assoc()): 
                                    ?>
                                        <tr>
                                            <td>
                                                <img src="/cerrodeliveryv2/assets/img/platos/<?php echo htmlspecialchars($plato['foto_url']); ?>" alt="<?php echo htmlspecialchars($plato['nombre_plato']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 0.5rem;">
                                                </td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($plato['nombre_plato']); ?></td>
                                            <td class="small text-muted"><?php echo htmlspecialchars($plato['descripcion']); ?></td>
                                            <td class="text-end">S/ <?php echo number_format($plato['precio'], 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center p-4 text-muted">Aún no has añadido ningún plato a tu menú.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div> </div> <?php
// --- Cierres de conexión (SIN CAMBIOS) ---
$stmt_platos->close();
$stmt_count->close();
$conn->close();
include '../includes/footer.php';
?>