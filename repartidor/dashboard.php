<?php
session_start();
// Seguridad
if (!isset($_SESSION['repartidor_id'])) {
    header('Location: ../login_repartidor.php');
    exit();
}
require_once '../includes/conexion.php';
include '../includes/header.php';
?>

<div class="container my-5">
    <div class="dashboard-header d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 fw-bold">Pedidos Disponibles</h1>
            <p class="text-muted mb-0">Hola, <?php echo htmlspecialchars($_SESSION['repartidor_nombre']); ?>. ¡Nuevas oportunidades te esperan!</p>
        </div>
        <div class="d-flex align-items-center mt-3 mt-md-0">
            <a href="mis_entregas.php" id="btn-mis-entregas" class="btn btn-primary me-2"><i class="bi bi-truck me-2"></i>Ver Mis Entregas</a>
            <a href="../procesos/logout_repartidor.php" class="btn btn-outline-danger"><i class="bi bi-box-arrow-right"></i>Cerrar sesion</a>
        </div>
    </div>

    <div id="pedidos-disponibles-container">
        <div class="text-center p-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Buscando pedidos...</span>
            </div>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('pedidos-disponibles-container');
    const cargarPedidos = () => {
        fetch('ajax_cargar_pedidos.php')
            .then(response => response.text())
            .then(html => { container.innerHTML = html; })
            .catch(error => console.error('Error al cargar pedidos:', error));
    };
    cargarPedidos();
    setInterval(cargarPedidos, 5000);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnMisEntregas = document.getElementById('btn-mis-entregas');
    const checkNotificaciones = () => {
        fetch('ajax_check_notificaciones.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'nueva_entrega') {
                    btnMisEntregas.innerHTML = 'Ver Mis Entregas <span class="badge bg-danger ms-1">1</span>';
                    btnMisEntregas.classList.add('btn-pulse');
                    fetch('../procesos/marcar_notificacion_vista.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ id_solicitud: data.id_solicitud })
                    });
                    setTimeout(() => { window.location.href = 'mis_entregas.php'; }, 2000);
                }
            })
            .catch(error => console.error('Error al verificar notificaciones:', error));
    };
    setInterval(checkNotificaciones, 4000);
});
</script>

<style>
.btn-pulse { animation: pulse 1s infinite; }
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}
</style>