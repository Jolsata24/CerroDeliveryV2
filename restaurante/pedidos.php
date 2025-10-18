<?php
session_start();
if (!isset($_SESSION['restaurante_id'])) {
    header('Location: ../login_restaurante.php');
    exit();
}
require_once '../includes/conexion.php';
include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-5 fw-bold mb-0">Gestión de Pedidos</h1>
    </div>

    <div id="pedidos-container">
        <div class="text-center p-5">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Cargando pedidos...</span>
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

    const cargarPedidos = () => {
        fetch('ajax_cargar_pedidos.php')
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
            })
            .catch(error => {
                console.error('Error al cargar la lista de pedidos:', error);
                container.innerHTML = '<div class="alert alert-danger">Error: No se pudo actualizar la lista de pedidos.</div>';
            });
    };

    cargarPedidos();
    setInterval(cargarPedidos, 6000);
});
</script>