<?php
session_start();
if (!isset($_SESSION['restaurante_id'])) {
    header('Location: ../login_restaurante.php');
    exit();
}
require_once '../includes/conexion.php';
include '../includes/header.php';
?>

<h1 class="mb-4">Gestión de Pedidos</h1>

<div id="pedidos-container">
    <div class="text-center p-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Cargando pedidos...</span>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('pedidos-container');

    const cargarPedidos = () => {
        // Llama a un archivo que genera la lista completa de pedidos
        fetch('ajax_cargar_pedidos.php')
            .then(response => response.text())
            .then(html => {
                // Reemplaza el contenido del contenedor con la nueva lista
                container.innerHTML = html;
            })
            .catch(error => {
                console.error('Error al cargar la lista de pedidos:', error);
                container.innerHTML = '<div class="alert alert-danger">Error: No se pudo actualizar la lista de pedidos.</div>';
            });
    };

    // Carga los pedidos la primera vez que entras a la página
    cargarPedidos();

    // Repite la carga cada 6 segundos para mantenerla siempre actualizada
    setInterval(cargarPedidos, 6000);
});
</script>