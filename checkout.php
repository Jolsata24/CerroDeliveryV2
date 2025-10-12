<?php
session_start();

// El guardia de seguridad de la página
if (!isset($_SESSION['cliente_id'])) {
    // Si no hay sesión de cliente, lo expulsamos a la página de login
    header('Location: login_cliente.php');
    exit();
}

// Si el código continúa, es porque el cliente sí ha iniciado sesión.
include 'includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Finalizar Pedido</h1>
    <div class="row">
        <div class="col-md-6">
            <h3>Resumen de tu Carrito</h3>
            <div id="resumen-carrito" class="card">
                <div class="card-body">
                    <p class="text-muted">Tu carrito está vacío.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <h3>Tus Datos para la Entrega</h3>
            <p>Hola, <strong><?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?></strong>. Confirma la dirección para este pedido.</p>

            <form action="procesos/procesar_pedido.php" method="POST">
                <input type="hidden" name="carrito_data" id="carrito_data">

                <div class="mb-3">
                    <label for="direccion_pedido" class="form-label">Dirección de Entrega</label>
                    <textarea class="form-control" id="direccion_pedido" name="direccion_pedido" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-success w-100">Confirmar y Realizar Pedido</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Script para mostrar el resumen del carrito en la página de checkout
    document.addEventListener('DOMContentLoaded', function() {
        const carrito = JSON.parse(sessionStorage.getItem('carrito')) || [];
        const resumenDiv = document.getElementById('resumen-carrito').querySelector('.card-body');
        const carritoDataInput = document.getElementById('carrito_data');

        if (carrito.length > 0) {
            resumenDiv.innerHTML = ''; // Limpiar el mensaje de "carrito vacío"
            let total = 0;
            const ul = document.createElement('ul');
            ul.className = 'list-group list-group-flush';

            carrito.forEach(item => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.textContent = `${item.nombre} x ${item.cantidad}`;

                const span = document.createElement('span');
                span.className = 'badge bg-primary rounded-pill';
                const subtotal = item.cantidad * item.precio;
                span.textContent = `S/ ${subtotal.toFixed(2)}`;
                total += subtotal;

                li.appendChild(span);
                ul.appendChild(li);
            });

            const totalLi = document.createElement('li');
            totalLi.className = 'list-group-item d-flex justify-content-between align-items-center fw-bold';
            totalLi.textContent = 'Total';
            const totalSpan = document.createElement('span');
            totalSpan.textContent = `S/ ${total.toFixed(2)}`;
            totalLi.appendChild(totalSpan);
            ul.appendChild(totalLi);

            resumenDiv.appendChild(ul);

            // Prepara los datos del carrito para ser enviados con el formulario
            carritoDataInput.value = JSON.stringify(carrito);
            // Aquí necesitaríamos el ID del restaurante, asumimos que lo podemos obtener de alguna manera
            // Por simplicidad, lo dejaremos para el procesador, pero en un caso real se pasaría desde la página de menú
        }
    });
</script>

<?php include 'includes/footer.php'; ?>