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

<div class="dashboard-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="h3">Pedidos Disponibles para Postular</h2>
        <p class="text-muted mb-0">Hola, <?php echo htmlspecialchars($_SESSION['repartidor_nombre']); ?>.</p>
    </div>
    <div>
        <a href="mis_entregas.php" id="btn-mis-entregas" class="btn btn-info text-white me-2">Ver Mis Entregas</a>
        <a href="../procesos/logout_repartidor.php" class="btn btn-outline-danger">Cerrar Sesión</a>
    </div>
</div>

<div id="pedidos-disponibles-container">
    <div class="text-center p-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Buscando pedidos...</span>
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
        // Llama a un archivo AJAX que generará la lista
        fetch('ajax_cargar_pedidos.php')
            .then(response => response.text())
            .then(html => {
                // Reemplaza el contenido del contenedor con la lista actualizada
                container.innerHTML = html;
            })
            .catch(error => {
                console.error('Error al cargar pedidos:', error);
                container.innerHTML = '<div class="alert alert-danger">No se pudo actualizar la lista de pedidos.</div>';
            });
    };

    // Carga los pedidos la primera vez
    cargarPedidos();

    // Repite la carga cada 5 segundos
    setInterval(cargarPedidos, 5000);
});
</script>

</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnMisEntregas = document.getElementById('btn-mis-entregas');

    const checkNotificaciones = () => {
        fetch('ajax_check_notificaciones.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'nueva_entrega') {
                    // 1. Añadir una notificación visual al botón
                    btnMisEntregas.innerHTML = 'Ver Mis Entregas <span class="badge bg-danger ms-1">1</span>';
                    btnMisEntregas.classList.add('btn-pulse'); // Animación

                    // 2. Marcar la notificación como vista para no repetirla
                    fetch('../procesos/marcar_notificacion_vista.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ id_solicitud: data.id_solicitud })
                    });

                    // 3. Después de 2 segundos, redirigir al repartidor
                    setTimeout(() => {
                        window.location.href = 'mis_entregas.php';
                    }, 2000);
                }
            })
            .catch(error => console.error('Error al verificar notificaciones:', error));
    };

    // Verificar notificaciones cada 4 segundos
    setInterval(checkNotificaciones, 4000);
});
</script>

<style>
.btn-pulse {
    animation: pulse 1s infinite;
}
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
}
</style>