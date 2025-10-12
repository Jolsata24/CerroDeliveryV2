<?php
// 1. INICIAR SESIÓN Y SEGURIDAD
// ==============================
session_start();

// El "guardia de seguridad": si el ID del restaurante no está en la sesión,
// lo redirigimos a la página de login.
if (!isset($_SESSION['restaurante_id'])) {
    header('Location: ../login_restaurante.php');
    exit();
}

// 2. CONEXIÓN A LA BASE DE DATOS Y CONSULTAS
// ==========================================
require_once '../includes/conexion.php';

$id_restaurante_actual = $_SESSION['restaurante_id'];

// --- Consulta para obtener los platos del menú de este restaurante ---
$sql_platos = "SELECT * FROM menu_platos WHERE id_restaurante = ? ORDER BY id DESC";
$stmt_platos = $conn->prepare($sql_platos);
$stmt_platos->bind_param("i", $id_restaurante_actual);
$stmt_platos->execute();
$resultado_platos = $stmt_platos->get_result();

// --- NUEVO: Consulta para contar los pedidos pendientes ---
$sql_count = "SELECT COUNT(id) AS total_pendientes FROM pedidos WHERE id_restaurante = ? AND estado_pedido = 'Pendiente'";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $id_restaurante_actual);
$stmt_count->execute();
$resultado_count = $stmt_count->get_result();
$row_count = $resultado_count->fetch_assoc();
$total_pendientes = $row_count['total_pendientes'];


// 3. INCLUIR LA CABECERA HTML
// ============================
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['restaurante_nombre']); ?></h2>
        <p class="text-muted">Tu centro de control para gestionar tu negocio en CerroDelivery.</p>
    </div>
    <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
</div>

<div class="row mb-4 g-3">
    <div class="col-md-6">
        <div class="card text-center h-100">
            <div class="card-body">
                <h5 class="card-title">Gestionar Menú</h5>
                <p class="card-text">Añade, edita o elimina los platos de tu carta.</p>
                <a href="#menu-management" class="btn btn-secondary">Ir a Mi Menú</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-center bg-warning h-100">
            <div class="card-body">
                <h5 class="card-title">
                    Pedidos Pendientes
                    <?php if ($total_pendientes > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?php echo $total_pendientes; ?></span>
                    <?php endif; ?>
                </h5>
                <p class="card-text">Revisa las nuevas órdenes de tus clientes.</p>
                <a href="pedidos.php" class="btn btn-dark stretched-link">Ver Pedidos</a>
            </div>
        </div>
    </div>
</div>


<div class="card mb-5" id="menu-management">
    <div class="card-header">
        <h3>Añadir un Nuevo Plato</h3>
    </div>
    <div class="card-body">
        <form action="../procesos/procesar_agregar_plato.php" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre_plato" class="form-label">Nombre del Plato</label>
                    <input type="text" class="form-control" id="nombre_plato" name="nombre_plato" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="precio" class="form-label">Precio (S/)</label>
                    <input type="number" step="0.10" class="form-control" id="precio" name="precio" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción del Plato</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
            </div>
            <div class="mb-3">
                <label for="foto" class="form-label">Foto del Plato (Opcional)</label>
                <input class="form-control" type="file" id="foto" name="foto">
            </div>
            <button type="submit" class="btn btn-primary">Añadir Plato al Menú</button>
        </form>
    </div>
</div>


<div class="card">
    <div class="card-header">
        <h3>Tu Menú Actual</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado_platos->num_rows > 0): ?>
                        <?php while($plato = $resultado_platos->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <img src="../assets/img/platos/<?php echo htmlspecialchars($plato['foto_url']); ?>" alt="<?php echo htmlspecialchars($plato['nombre_plato']); ?>" width="100" class="img-thumbnail">
                                </td>
                                <td><?php echo htmlspecialchars($plato['nombre_plato']); ?></td>
                                <td><?php echo htmlspecialchars($plato['descripcion']); ?></td>
                                <td>S/ <?php echo number_format($plato['precio'], 2); ?></td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="#" class="btn btn-sm btn-danger">Eliminar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center p-4">Aún no has añadido ningún plato. ¡Usa el formulario de arriba para empezar!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// 7. CERRAR CONEXIONES Y FOOTER
// ==============================
$stmt_platos->close();
$stmt_count->close();
$conn->close();
include '../includes/footer.php';
?>