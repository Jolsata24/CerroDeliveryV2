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

                            <div class="mb-4">
                                <label class="form-label fw-bold"><i class="bi bi-geo-alt-fill me-2"></i>Ubicación de Entrega</label>

                                <div id="mapa-checkout" style="height: 250px; width: 100%; border-radius: 10px; margin-bottom: 10px;" class="border"></div>
                                <div class="form-text mb-2 text-primary"><i class="bi bi-info-circle"></i> Mueve el pin rojo para ajustar tu ubicación exacta.</div>

                                <div class="d-grid mb-3">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="usar-gps-btn">
                                        <i class="bi bi-crosshair me-1"></i> Detectar mi ubicación (GPS)
                                    </button>
                                    <div id="gps-status" class="form-text text-center"></div>
                                </div>

                                <label for="direccion_pedido" class="form-label small text-muted">Referencia escrita (Torre, Piso, Color de puerta)</label>
                                <textarea class="form-control" id="direccion_pedido" name="direccion_pedido" rows="2" required placeholder="Ej: Casa verde frente al parque..."></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold"><i class="bi bi-wallet2 me-2"></i>Método de Pago</label>
                                <select class="form-select mb-3" id="metodo_pago" name="metodo_pago" required>
                                    <option value="" selected disabled>Selecciona cómo pagar</option>
                                    <option value="yape">Yape / Plin</option>
                                    <option value="tarjeta">Tarjeta (POS)</option>
                                    <option value="efectivo">Efectivo</option>
                                </select>
                                <div id="info-yape-container" class="card mb-3 border-primary" style="display: none; background-color: #f8f9fa;">
                                    <div class="card-body text-center">
                                        <h6 class="text-primary fw-bold mb-2"> <i class="bi bi-qr-code"></i> Escanea para pagar</h6>
                                        <div id="yape-qr-img-placeholder" class="mb-2"></div>
                                        <h5 class="fw-bold" id="yape-numero-display">Cargando...</h5>
                                        <div class="alert alert-warning py-1 small mt-2">
                                            Realiza el pago y espera confirmación.
                                        </div>
                                    </div>
                                </div>
                                <div id="div-vuelto" style="display: none;">
                                    <label for="monto_pagar" class="form-label small">¿Con cuánto vas a pagar?</label>
                                    <div class="input-group">
                                        <span class="input-group-text">S/</span>
                                        <input type="number" class="form-control" id="monto_pagar" name="monto_pagar" placeholder="Ej: 50.00" step="0.10">
                                    </div>
                                    <div class="form-text">Llevaremos el vuelto exacto.</div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg btn-confirm-order">
                                    Confirmar Pedido
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
        let carritoData = JSON.parse(sessionStorage.getItem(carritoKey)) || {
            items: [],
            restauranteId: null
        };
        let carrito = carritoData.items;
        $sql_rest = "SELECT latitud, longitud FROM restaurantes WHERE id = ?";

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
                    function(error) {
                        /* ... (manejo de errores sin cambios) ... */
                    }
                );
            } else {
                /* ... (manejo de errores sin cambios) ... */
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. CONFIGURACIÓN DEL MAPA ---
        const defaultLat = -10.683; // Cerro de Pasco
        const defaultLng = -76.256;
        let userLat = defaultLat;
        let userLng = defaultLng;

        const mapa = L.map('mapa-checkout').setView([defaultLat, defaultLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(mapa);
        
        // Marcador del cliente (movible)
        let marcador = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(mapa);

        // Variables para el cálculo (se llenarán via API)
        const TARIFA_BASE = 5.00;
        const KM_BASE = 1.5;
        const PRECIO_KM_EXTRA = 2.00;
        let restLat = null;
        let restLon = null;

        // --- 2. OBTENER DATOS DEL RESTAURANTE (COORDENADAS) ---
        // Recuperamos el ID del restaurante del carrito guardado
        const carritoKey = `carritoData_${CLIENTE_ID}`;
        const carritoData = JSON.parse(sessionStorage.getItem(carritoKey));

        if (carritoData && carritoData.restauranteId) {
            fetch(`procesos/obtener_datos_restaurante.php?id_restaurante=${carritoData.restauranteId}`)
                .then(response => response.json())
                .then(resp => {
                    if (resp.status === 'success' && resp.data.latitud && resp.data.longitud) {
                        restLat = parseFloat(resp.data.latitud);
                        restLon = parseFloat(resp.data.longitud);
                        console.log("Coordenadas restaurante cargadas:", restLat, restLon);
                        
                        // Recalcular envío inicial por si acaso
                        actualizarTotalesEnvio(); 
                    }
                })
                .catch(err => console.error("Error cargando ubicación restaurante:", err));
        }

        // --- 3. LÓGICA DE CÁLCULO DE DISTANCIA ---
        function calcularEnvio(clienteLat, clienteLon) {
            // Si el restaurante no ha configurado su ubicación, cobramos tarifa base
            if (!restLat || !restLon) return TARIFA_BASE;

            const R = 6371; // Radio tierra km
            const dLat = (clienteLat - restLat) * Math.PI / 180;
            const dLon = (clienteLon - restLon) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(restLat * Math.PI / 180) * Math.cos(clienteLat * Math.PI / 180) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            const distancia = R * c; 

            let costoEnvio = TARIFA_BASE;
            if (distancia > KM_BASE) {
                costoEnvio += (distancia - KM_BASE) * PRECIO_KM_EXTRA;
            }
            return Math.round(costoEnvio * 10) / 10;
        }

        function actualizarTotalesEnvio() {
            const costo = calcularEnvio(userLat, userLng);
            
            // Aquí intentamos buscar si ya existe la fila de envío en la tabla para actualizarla
            // Nota: Esto depende de que tu función renderCarrito() (en el otro script) cree la tabla primero.
            // Para simplificar, podrías mostrar el costo en un elemento separado o inyectarlo en la tabla.
            
            // Opción simple: Crear un div flotante o actualizar un elemento específico si existe
            console.log("Costo de envío calculado: S/", costo);
            
            // Agregar el costo al input oculto si tuvieras uno, o manejarlo visualmente
            // (La validación final y cobro real se hace en el servidor PHP)
            
            // Buscar la fila del total y actualizarla visualmente (Opcional avanzado)
            const filaTotal = document.querySelector('.total-row .h5');
            if(filaTotal) {
                // Esto es solo visual, para hacerlo bien deberías integrar esto en renderCarrito
                // Por ahora dejaremos que el PHP haga el cobro final real.
            }
        }

        function actualizarCoordenadas(lat, lng) {
            document.getElementById('latitud').value = lat;
            document.getElementById('longitud').value = lng;
            userLat = lat;
            userLng = lng;
            actualizarTotalesEnvio();
        }

        // Inicializar
        actualizarCoordenadas(defaultLat, defaultLng);

        marcador.on('dragend', function(event) {
            var position = marcador.getLatLng();
            actualizarCoordenadas(position.lat, position.lng);
        });

        // --- 4. GPS ---
        const gpsBtn = document.getElementById('usar-gps-btn');
        const gpsStatus = document.getElementById('gps-status');

        gpsBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
                gpsStatus.textContent = 'Buscando satélites...';
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;
                        mapa.setView([lat, lon], 16);
                        marcador.setLatLng([lat, lon]);
                        actualizarCoordenadas(lat, lon);
                        gpsStatus.innerHTML = '<span class="text-success">¡Ubicación encontrada!</span>';
                    },
                    function() { gpsStatus.innerHTML = '<span class="text-danger">Error GPS.</span>'; }
                );
            }
        });

        // --- 5. VISUALIZACIÓN DE PAGO ---
        const selectPago = document.getElementById('metodo_pago');
        const divVuelto = document.getElementById('div-vuelto');
        const inputVuelto = document.getElementById('monto_pagar');

        selectPago.addEventListener('change', function() {
            if (this.value === 'efectivo') {
                divVuelto.style.display = 'block';
                inputVuelto.setAttribute('required', 'true');
            } else {
                divVuelto.style.display = 'none';
                inputVuelto.removeAttribute('required');
                inputVuelto.value = '';
            }
        });

        setTimeout(() => mapa.invalidateSize(), 400);
    });
</script>

<?php include 'includes/footer.php'; ?>