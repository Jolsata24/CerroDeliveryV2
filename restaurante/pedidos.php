<?php
session_start();
if (!isset($_SESSION['restaurante_id'])) {
    header('Location: ../login_restaurante.php');
    exit();
}
require_once '../includes/conexion.php';
include '../includes/header.php';
?>

<div class="hero-quickbite">
    <div class="container hero-text">
        <div class="dashboard-header d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 fw-bold">Gestión de Pedidos</h1>
                <p class="lead text-white-50 mb-0">Revisa y actualiza el estado de tus pedidos aquí.</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-light mt-3 mt-md-0"><i class="bi bi-arrow-left me-2"></i>Volver al Panel</a>
        </div>
    </div>
</div>

<div class="main-content-overlay">
    <div class="container">
        <div id="pedidos-container">
            <div class="text-center p-5">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Cargando pedidos...</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include '../includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('pedidos-container');

    // --- FUNCIÓN NUEVA: Carga las solicitudes de un pedido específico ---
    const cargarSolicitudes = () => {
        // Busca todos los contenedores de solicitudes que se hayan cargado
        const contenedoresSolicitudes = document.querySelectorAll('.solicitudes-container');
        
        contenedoresSolicitudes.forEach(contenedor => {
            const idPedido = contenedor.dataset.idPedido;
            if (idPedido) {
                // Llama al PHP que carga la lista de repartidores para este pedido
                fetch(`ajax_cargar_solicitudes.php?id_pedido=${idPedido}`)
                    .then(response => response.text())
                    .then(html => {
                        contenedor.innerHTML = html;
                    })
                    .catch(error => console.error('Error al cargar solicitudes:', error));
            }
        });
    };

    // --- FUNCIÓN PRINCIPAL: Carga la lista de todos los pedidos ---
    const cargarPedidos = () => {
        fetch('ajax_cargar_pedidos.php')
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                // --- ¡CLAVE! Después de cargar los pedidos, llamamos a la función que carga las solicitudes ---
                cargarSolicitudes();
            })
            .catch(error => {
                console.error('Error al cargar la lista de pedidos:', error);
                container.innerHTML = '<div class="alert alert-danger">Error: No se pudo actualizar la lista de pedidos.</div>';
            });
    };

    // Carga inicial y actualización periódica
    cargarPedidos();
    setInterval(cargarPedidos, 6000); // Se actualiza cada 6 segundos
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('pedidos-container');

    // Función para cargar las solicitudes de repartidores
    const cargarSolicitudes = () => {
        const contenedoresSolicitudes = document.querySelectorAll('.solicitudes-container');
        contenedoresSolicitudes.forEach(contenedor => {
            const idPedido = contenedor.dataset.idPedido;
            if (idPedido) {
                fetch(`ajax_cargar_solicitudes.php?id_pedido=${idPedido}`)
                    .then(response => response.text())
                    .then(html => { contenedor.innerHTML = html; })
                    .catch(error => console.error('Error al cargar solicitudes:', error));
            }
        });
    };

    // --- FUNCIÓN PRINCIPAL MODIFICADA ---
    const cargarPedidos = () => {
        // 1. VERIFICACIÓN: Si hay un modal abierto (clase .show), NO actualizamos
        if (document.querySelector('.modal.show')) {
            console.log("Actualización pausada: Usuario viendo un comprobante.");
            return; 
        }

        fetch('ajax_cargar_pedidos.php')
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                cargarSolicitudes(); // Volver a cargar solicitudes tras actualizar
            })
            .catch(error => {
                console.error('Error al cargar pedidos:', error);
            });
    };

    // Carga inicial
    cargarPedidos();
    
    // Intervalo de actualización (cada 6 segundos)
    setInterval(cargarPedidos, 6000);
});
</script>