<?php
session_start();
if (!isset($_SESSION['repartidor_id'])) {
    header('Location: ../login_repartidor.php');
    exit();
}
include '../includes/header.php';
?>

<div class="dashboard-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="h3">Mis Entregas Activas</h2>
        <p class="text-muted mb-0">Pedidos que has aceptado y están en curso.</p>
    </div>
    <a href="dashboard.php" class="btn btn-outline-secondary">Volver a Pedidos Disponibles</a>
</div>

<div id="entregas-container">
    <div class="text-center p-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('entregas-container');
    
    // Función para actualizar la ubicación del repartidor en segundo plano
    const iniciarTracking = () => {
        if (navigator.geolocation) {
            navigator.geolocation.watchPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    fetch('../repartidor/actualizar_ubicacion_repartidor.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ lat: lat, lon: lon })
                    });
                }, 
                function(error){ console.warn("Error de geolocalización:", error.message); },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }
    };

    // Función para cargar la lista de entregas
    const cargarEntregas = () => {
        fetch('ajax_cargar_mis_entregas.php')
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                // Si hay entregas, nos aseguramos de que el tracking esté activo
                if (!html.includes("No tienes entregas activas")) {
                    iniciarTracking();
                }
            })
            .catch(error => console.error('Error al cargar entregas:', error));
    };

    // Carga inicial y luego cada 7 segundos
    cargarEntregas();
    setInterval(cargarEntregas, 7000);
});
</script>