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

<div class="container">
    <h1 class="mb-4">Finalizar Pedido</h1>
    <div class="row g-5">
        <div class="col-md-7">
            <h3>Resumen de tu Carrito</h3>
            <div id="resumen-carrito" class="card">
                </div>
        </div>
        <div class="col-md-5">
            <h3>Tus Datos para la Entrega</h3>
            <div class="card">
                <div class="card-body">
                    <p>Hola, <strong><?php echo htmlspecialchars($_SESSION['cliente_nombre']); ?></strong>.</p>
                    <form action="procesos/procesar_pedido.php" method="POST" id="checkout-form">
                        <input type="hidden" name="carrito_data" id="carrito_data">
                        <input type="hidden" name="id_restaurante" id="id_restaurante">
                        
                        <input type="hidden" name="latitud" id="latitud">
                        <input type="hidden" name="longitud" id="longitud">
                        
                        <div class="mb-3">
                            <label for="direccion_pedido" class="form-label">Dirección de Entrega:</label>
                            <textarea class="form-control" id="direccion_pedido" name="direccion_pedido" rows="3" required></textarea>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button type="button" class="btn btn-secondary" id="usar-gps-btn">
                                📍 Usar mi ubicación actual (GPS)
                            </button>
                            <div id="gps-status" class="form-text mt-1"></div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 btn-lg">Confirmar y Realizar Pedido</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// =================================================================
// SCRIPT CORREGIDO PARA EL CARRITO INTERACTIVO
// =================================================================
document.addEventListener('DOMContentLoaded', function() {
    const resumenDiv = document.getElementById('resumen-carrito').querySelector('.card-body');
    const carritoDataInput = document.getElementById('carrito_data');
    const restauranteIdInput = document.getElementById('id_restaurante'); // <-- Referencia al nuevo input
    const checkoutForm = document.getElementById('checkout-form');
    
    // CORRECCIÓN: Leer el objeto 'carritoData' que contiene items y restauranteId
    let carritoData = JSON.parse(sessionStorage.getItem('carritoData')) || { items: [], restauranteId: null };
    let carrito = carritoData.items;

    // --- FUNCIÓN PRINCIPAL PARA DIBUJAR EL CARRITO ---
    function renderCarrito() {
        resumenDiv.innerHTML = '';
        
        if (carrito.length === 0) {
            resumenDiv.innerHTML = '<p class="text-muted">Tu carrito está vacío.</p>';
            checkoutForm.style.display = 'none';
            return;
        }
        
        checkoutForm.style.display = 'block';
        let total = 0;
        const tabla = document.createElement('table');
        tabla.className = 'table align-middle';
        tabla.innerHTML = `
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-end">Subtotal</th>
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
                <td>${item.nombre}</td>
                <td class="text-center">
                    <div class="input-group input-group-sm justify-content-center">
                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="modificarCantidad('${item.id}', -1)">-</button>
                        <span class="input-group-text">${item.cantidad}</span>
                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="modificarCantidad('${item.id}', 1)">+</button>
                    </div>
                </td>
                <td class="text-end">S/ ${subtotal.toFixed(2)}</td>
                <td class="text-center">
                    <button class="btn btn-danger btn-sm" type="button" onclick="eliminarItem('${item.id}')">X</button>
                </td>
            `;
            tbody.appendChild(fila);
        });

        const tfoot = tabla.querySelector('tfoot');
        tfoot.innerHTML = `
            <tr>
                <td colspan="2" class="text-end fw-bold">Total</td>
                <td class="text-end fw-bold h5">S/ ${total.toFixed(2)}</td>
                <td></td>
            </tr>
        `;

        resumenDiv.appendChild(tabla);
        // CORRECCIÓN: Actualizar AMBOS inputs ocultos
        carritoDataInput.value = JSON.stringify(carrito);
        restauranteIdInput.value = carritoData.restauranteId;
    }

    // --- FUNCIONES PARA MANIPULAR EL CARRITO ---
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
        // CORRECCIÓN: Guardar el objeto completo, no solo los items
        carritoData.items = carrito;
        sessionStorage.setItem('carritoData', JSON.stringify(carritoData));
        renderCarrito();
    }

    renderCarrito();
});
</script>
<script>
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
                    // Éxito: se obtuvieron las coordenadas
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;

                    // Llenamos los inputs ocultos
                    latitudInput.value = lat;
                    longitudInput.value = lon;

                    // Llenamos el textarea para que el usuario vea algo
                    direccionTextarea.value = `Ubicación precisa obtenida por GPS (Lat: ${lat.toFixed(4)}, Lon: ${lon.toFixed(4)})`;
                    
                    gpsStatus.textContent = '¡Ubicación obtenida con éxito!';
                    gpsStatus.className = 'text-success';
                },
                function(error) {
                    // Manejo de errores
                    let mensajeError;
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            mensajeError = "Permiso de ubicación denegado.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            mensajeError = "La información de ubicación no está disponible.";
                            break;
                        case error.TIMEOUT:
                            mensajeError = "La solicitud de ubicación ha caducado.";
                            break;
                        default:
                            mensajeError = "Ocurrió un error desconocido.";
                            break;
                    }
                    gpsStatus.textContent = mensajeError;
                    gpsStatus.className = 'text-danger';
                }
            );
        } else {
            gpsStatus.textContent = "La geolocalización no es compatible con este navegador.";
            gpsStatus.className = 'text-danger';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>