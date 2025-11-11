<?php
session_start();

// El guardia de seguridad de la página
if (!isset($_SESSION['cliente_id'])) {
    header('Location: login_cliente.php');
    exit();
}

// Si el código continúa, es porque el cliente sí ha iniciado sesión.
include 'includes/header.php';
?>

<div class="hero-quickbite">
    <div class="container hero-text text-center">
        <h1 class="display-4 fw-bold">Ya casi está listo tu pedido</h1>
        <p class="lead text-white-50">Solo necesitamos unos datos más para la entrega.</p>
    </div>
</div>

<div class="main-content-overlay">
    <div class="container"> 
        
        <div class="mb-4">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Seguir Comprando
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card checkout-card">
                    <div class="card-header">
                        <h4 class="mb-0">🛒 Resumen de tu Carrito</h4>
                    </div>
                    <div id="resumen-carrito" class="card-body p-0">
                        <div class="p-4 text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card checkout-card">
                    <div class="card-header">
                        <h4 class="mb-0">📝 Tus Datos para la Entrega</h4>
                    </div>
                    <div class="card-body">
                        <p class="lead fs-6">Hola, <strong><?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?></strong>.</p>
                        <form action="procesos/procesar_pedido.php" method="POST" id="checkout-form">
                            <input type="hidden" name="carrito_data" id="carrito_data">
                            <input type="hidden" name="id_restaurante" id="id_restaurante">
                            <input type="hidden" name="latitud" id="latitud">
                            <input type="hidden" name="longitud" id="longitud">
                            
                            <div class="mb-3">
                                <label for="direccion_pedido" class="form-label fw-bold">Dirección de Entrega Completa</label>
                                <textarea class="form-control" id="direccion_pedido" name="direccion_pedido" rows="3" required placeholder="Ej: Av. Principal 123, Apto 4, Urb. Las Flores..."></textarea>
                            </div>
                            
                            <div class="d-grid mb-4">
                                <button type="button" class="btn btn-gps" id="usar-gps-btn">
                                    <i class="bi bi-geo-alt-fill me-2"></i> Usar mi ubicación actual (GPS)
                                </button>
                                <div id="gps-status" class="form-text mt-2 text-center"></div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg btn-confirm-order">
                                    Confirmar y Realizar Pedido
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Variable CLIENTE_ID (sin cambios)
    const CLIENTE_ID = <?php echo isset($_SESSION['cliente_id']) ? json_encode($_SESSION['cliente_id']) : 'null'; ?>;
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const resumenDiv = document.getElementById('resumen-carrito');
    const carritoDataInput = document.getElementById('carrito_data');
    const restauranteIdInput = document.getElementById('id_restaurante');
    const checkoutForm = document.getElementById('checkout-form');
    
    const carritoKey = `carritoData_${CLIENTE_ID}`;
    let carritoData = JSON.parse(sessionStorage.getItem(carritoKey)) || { items: [], restauranteId: null };
    let carrito = carritoData.items;

    function renderCarrito() {
        resumenDiv.innerHTML = '';
        
        if (carrito.length === 0) {
            resumenDiv.innerHTML = '<div class="p-4 text-center text-muted">Tu carrito está vacío.</div>';
            checkoutForm.style.display = 'none';
            return;
        }
        
        checkoutForm.style.display = 'block';
        let total = 0;

        // === INICIO DE LA CORRECCIÓN RESPONSIVE ===
        // 1. Creamos un 'div' que envolverá la tabla
        const responsiveWrapper = document.createElement('div');
        responsiveWrapper.className = 'table-responsive';
        // === FIN DE LA CORRECCIÓN RESPONSIVE ===

        const tabla = document.createElement('table');
        tabla.className = 'table table-borderless align-middle summary-table';
        tabla.innerHTML = `
            <thead class="table-light">
                <tr>
                    <th scope="col" class="ps-4">Producto</th>
                    <th scope="col" class="text-center">Cantidad</th>
                    <th scope="col" class="text-end pe-4">Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot></tfoot>
        `;
        
        const tbody = tabla.querySelector('tbody');
        carrito.forEach(item => {
            const subtotal = item.cantidad * item.precio;
            total += subtotal;
            const fila = document.createElement('tr');
            fila.innerHTML = `
                <td class="ps-4">${item.nombre}</td>
                <td class="text-center">
                    <div class="input-group input-group-sm justify-content-center">
                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="modificarCantidad('${item.id}', -1)">-</button>
                        <span class="input-group-text">${item.cantidad}</span>
                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="modificarCantidad('${item.id}', 1)">+</button>
                    </div>
                </td>
                <td class="text-end pe-4">S/ ${subtotal.toFixed(2)}</td>
                <td class="text-center">
                    <button class="btn btn-outline-danger btn-sm rounded-circle" type="button" onclick="eliminarItem('${item.id}')" style="width: 30px; height: 30px;">X</button>
                </td>
            `;
            tbody.appendChild(fila);
        });

        const tfoot = tabla.querySelector('tfoot');
        tfoot.innerHTML = `
            <tr class="total-row">
                <td colspan="2" class="text-end fw-bold ps-4">Total a Pagar</td>
                <td class="text-end fw-bold h5 pe-4">S/ ${total.toFixed(2)}</td>
                <td></td>
            </tr>
        `;

        // === INICIO DE LA CORRECCIÓN RESPONSIVE ===
        // 2. Añadimos la tabla al 'wrapper' responsivo
        responsiveWrapper.appendChild(tabla);
        // 3. Añadimos el 'wrapper' (que contiene la tabla) al div principal
        resumenDiv.appendChild(responsiveWrapper);
        // === FIN DE LA CORRECCIÓN RESPONSIVE ===
        
        carritoDataInput.value = JSON.stringify(carrito);
        restauranteIdInput.value = carritoData.restauranteId;
    }

    // --- LÓGICA DE MODIFICAR Y ELIMINAR (SIN CAMBIOS) ---
    window.modificarCantidad = function(idPlato, cambio) {
        const item = carrito.find(i => i.id === idPlato);
        if (item) {
            item.cantidad += cambio;
            if (item.cantidad <= 0) {
                eliminarItem(idPlato);
            } else {
                guardarYRenderizar();
            }
        }
    }

    window.eliminarItem = function(idPlato) {
        carrito = carrito.filter(i => i.id !== idPlato);
        guardarYRenderizar();
    }

    function guardarYRenderizar() {
        carritoData.items = carrito;
        sessionStorage.setItem(carritoKey, JSON.stringify(carritoData));
        renderCarrito();
    }

    renderCarrito();
});
</script>
<script>
// --- SCRIPT DEL GPS (SIN CAMBIOS) ---
document.addEventListener('DOMContentLoaded', function() {
    const gpsBoton = document.getElementById('usar-gps-btn');
    const direccionTextarea = document.getElementById('direccion_pedido');
    const latitudInput = document.getElementById('latitud');
    const longitudInput = document.getElementById('longitud');
    const gpsStatus = document.getElementById('gps-status');

    gpsBoton.addEventListener('click', function() {
        if (navigator.geolocation) {
            gpsStatus.textContent = 'Obteniendo tu ubicación...';
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    latitudInput.value = lat;
                    longitudInput.value = lon;
                    direccionTextarea.value = `Ubicación precisa obtenida por GPS (Lat: ${lat.toFixed(4)}, Lon: ${lon.toFixed(4)})`;
                    gpsStatus.innerHTML = '<strong>¡Ubicación obtenida con éxito!</strong>';
                    gpsStatus.className = 'text-success mt-2 text-center';
                },
                function(error) { /* ... (manejo de errores sin cambios) ... */ }
            );
        } else { /* ... (manejo de errores sin cambios) ... */ }
    });
});
</script>

<?php include 'includes/footer.php'; ?>