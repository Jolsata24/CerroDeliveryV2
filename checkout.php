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
                                        <h6 class="text-primary fw-bold mb-3">
                                            <i class="bi bi-qr-code-scan"></i> Escanea el QR o usa el número
                                        </h6>

                                        <div id="yape-qr-img-placeholder" class="mb-3 d-flex justify-content-center">
                                        </div>

                                        <p class="mb-1 text-muted small">Número asociado:</p>

                                        <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                                            <h3 class="fw-bold mb-0 text-dark" id="yape-numero-display">...</h3>

                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-circle" id="btn-copiar-yape" title="Copiar número" style="width: 38px; height: 38px;">
                                                <i class="bi bi-clipboard-check"></i>
                                            </button>
                                        </div>

                                        <div id="mensaje-copia" class="badge bg-success mb-2" style="display:none;">
                                            ¡Número copiado!
                                        </div>

                                        <div class="alert alert-warning py-2 small mt-2 mb-0 border-0 bg-warning bg-opacity-10 text-warning-emphasis">
                                            <i class="bi bi-info-circle-fill me-1"></i> Realiza el pago y espera la confirmación del restaurante.
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
        // --- VARIABLES GLOBALES ---
        const selectPago = document.getElementById('metodo_pago');
        const containerYape = document.getElementById('info-yape-container');
        const displayYapeNum = document.getElementById('yape-numero-display');
        const displayYapeQR = document.getElementById('yape-qr-img-placeholder');
        const divVuelto = document.getElementById('div-vuelto');
        const inputVuelto = document.getElementById('monto_pagar');

        // Variables de datos del restaurante
        let datosRestaurante = {
            lat: null,
            lon: null,
            yapeNumero: '',
            yapeQR: ''
        };

        // Configuración mapa
        const defaultLat = -10.683;
        const defaultLng = -76.256;
        let userLat = defaultLat;
        let userLng = defaultLng;

        const mapa = L.map('mapa-checkout').setView([defaultLat, defaultLng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapa);
        let marcador = L.marker([defaultLat, defaultLng], {
            draggable: true
        }).addTo(mapa);

        // --- 1. OBTENER DATOS DEL RESTAURANTE (COORDENADAS Y YAPE) ---
        const carritoKey = `carritoData_${CLIENTE_ID}`;
        const carritoData = JSON.parse(sessionStorage.getItem(carritoKey));

        if (carritoData && carritoData.restauranteId) {
            fetch(`procesos/obtener_datos_restaurante.php?id_restaurante=${carritoData.restauranteId}`)
                .then(response => response.json())
                .then(resp => {
                    if (resp.status === 'success') {
                        // Guardamos los datos recibidos
                        datosRestaurante.lat = parseFloat(resp.data.latitud);
                        datosRestaurante.lon = parseFloat(resp.data.longitud);
                        datosRestaurante.yapeNumero = resp.data.yape_numero;
                        datosRestaurante.yapeQR = resp.data.yape_qr;

                        // Recalcular envío ahora que tenemos coordenadas del restaurante
                        actualizarTotalesEnvio();
                    }
                })
                .catch(err => console.error("Error datos restaurante:", err));
        }

        // --- 2. LÓGICA DE PAGO (YAPE / EFECTIVO) ---
        selectPago.addEventListener('change', function() {
            // Resetear visualización
            containerYape.style.display = 'none';
            divVuelto.style.display = 'none';
            inputVuelto.removeAttribute('required');

            if (this.value === 'yape') {
                containerYape.style.display = 'block';
                displayYapeNum.textContent = datosRestaurante.yapeNumero || "Sin número registrado";

                if (datosRestaurante.yapeQR) {
                    displayYapeQR.innerHTML = `<img src="assets/img/qr/${datosRestaurante.yapeQR}" class="img-fluid rounded border" style="max-width: 200px;">`;
                } else {
                    displayYapeQR.innerHTML = '<span class="text-muted small">Sin código QR</span>';
                }

            } else if (this.value === 'efectivo') {
                divVuelto.style.display = 'block';
                inputVuelto.setAttribute('required', 'true');
            }
        });

        // --- 3. CÁLCULO DE ENVÍO DINÁMICO ---
        function calcularEnvio(clienteLat, clienteLon) {
            if (!datosRestaurante.lat || !datosRestaurante.lon) return 5.00; // Default si no hay mapa

            const R = 6371;
            const dLat = (clienteLat - datosRestaurante.lat) * Math.PI / 180;
            const dLon = (clienteLon - datosRestaurante.lon) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(datosRestaurante.lat * Math.PI / 180) * Math.cos(clienteLat * Math.PI / 180) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            const distancia = R * c;

            let costo = 5.00; // Tarifa base
            if (distancia > 1.5) { // Si pasa de 1.5km
                costo += (distancia - 1.5) * 2.00; // S/ 2.00 por km extra
            }
            return Math.round(costo * 10) / 10;
        }

        function actualizarTotalesEnvio() {
            const costoEnvio = calcularEnvio(userLat, userLng);

            // Actualizar visualmente la tabla (Buscamos la fila del total)
            // Nota: Esto asume que el script de renderizado de tabla ya corrió
            const celdaTotal = document.querySelector('.total-row .h5');

            if (celdaTotal) {
                // Recalculamos sumando lo que ya hay en el carrito (truco rápido)
                // Lo ideal es regenerar la tabla, pero para no complicar tu código:
                // Vamos a leer el subtotal de items del sessionStorage
                let totalProductos = 0;
                if (carritoData && carritoData.items) {
                    carritoData.items.forEach(i => totalProductos += i.precio * i.cantidad);
                }

                const totalFinal = totalProductos + costoEnvio;

                // Insertamos una fila de envío si no existe
                let tfoot = document.querySelector('.summary-table tfoot');
                let rowEnvio = document.getElementById('row-envio-dinamico');

                if (!rowEnvio && tfoot) {
                    rowEnvio = document.createElement('tr');
                    rowEnvio.id = 'row-envio-dinamico';
                    rowEnvio.innerHTML = `<td colspan="2" class="text-end text-muted">Costo de Envío (Aprox.)</td><td class="text-end text-muted" id="val-envio"></td><td></td>`;
                    tfoot.insertBefore(rowEnvio, tfoot.firstChild); // Poner antes del total
                }

                if (document.getElementById('val-envio')) {
                    document.getElementById('val-envio').textContent = `S/ ${costoEnvio.toFixed(2)}`;
                }

                celdaTotal.textContent = `S/ ${totalFinal.toFixed(2)}`;
            }

            // Actualizamos los inputs ocultos para enviarlos al PHP
            document.getElementById('latitud').value = userLat;
            document.getElementById('longitud').value = userLng;
        }

        // Eventos del mapa
        marcador.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            userLat = pos.lat;
            userLng = pos.lng;
            actualizarTotalesEnvio();
        });

        // Inicializar
        setTimeout(() => {
            mapa.invalidateSize();
        }, 500);
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- ELEMENTOS DEL DOM ---
    const selectPago = document.getElementById('metodo_pago');
    const containerYape = document.getElementById('info-yape-container');
    const displayYapeNum = document.getElementById('yape-numero-display');
    const displayYapeQR = document.getElementById('yape-qr-img-placeholder');
    const btnCopiar = document.getElementById('btn-copiar-yape');
    const msgCopia = document.getElementById('mensaje-copia');
    
    const divVuelto = document.getElementById('div-vuelto');
    const inputVuelto = document.getElementById('monto_pagar');
    
    // Objeto para guardar los datos del restaurante
    let datosRestaurante = {
        yapeNumero: '',
        yapeQR: '',
        lat: null, // Para el cálculo de envío (si lo estás usando)
        lon: null
    };

    // --- 1. CARGAR DATOS DEL RESTAURANTE AL INICIO ---
    // Usamos el ID del cliente definido en PHP
    const carritoKey = `carritoData_${CLIENTE_ID}`;
    const carritoData = JSON.parse(sessionStorage.getItem(carritoKey));

    if (carritoData && carritoData.restauranteId) {
        // Llamamos a tu API para obtener los datos reales (QR, Número, Ubicación)
        fetch(`procesos/obtener_datos_restaurante.php?id_restaurante=${carritoData.restauranteId}`)
            .then(response => response.json())
            .then(resp => {
                if (resp.status === 'success') {
                    datosRestaurante.yapeNumero = resp.data.yape_numero;
                    datosRestaurante.yapeQR = resp.data.yape_qr;
                    datosRestaurante.lat = parseFloat(resp.data.latitud);
                    datosRestaurante.lon = parseFloat(resp.data.longitud);
                    
                    // Si tienes la función de costo de envío dinámico, llámala aquí:
                    if (typeof actualizarTotalesEnvio === 'function') {
                        actualizarTotalesEnvio(); 
                    }
                }
            })
            .catch(err => console.error("Error cargando datos del restaurante:", err));
    }

    // --- 2. LÓGICA DE CAMBIO DE MÉTODO DE PAGO ---
    selectPago.addEventListener('change', function() {
        // Ocultamos todo por defecto para limpiar la vista
        containerYape.style.display = 'none';
        divVuelto.style.display = 'none';
        inputVuelto.removeAttribute('required');

        if (this.value === 'yape') {
            // MOSTRAR YAPE
            containerYape.style.display = 'block';
            
            // Inyectar el número
            displayYapeNum.textContent = datosRestaurante.yapeNumero || "No registrado";
            
            // Inyectar la imagen del QR
            if (datosRestaurante.yapeQR) {
                displayYapeQR.innerHTML = `<img src="assets/img/qr/${datosRestaurante.yapeQR}" class="img-fluid rounded shadow-sm border" style="max-width: 220px;">`;
            } else {
                displayYapeQR.innerHTML = '<div class="p-3 bg-light rounded text-muted border">Sin código QR disponible</div>';
            }

        } else if (this.value === 'efectivo') {
            // MOSTRAR OPCIÓN DE VUELTO
            divVuelto.style.display = 'block';
            inputVuelto.setAttribute('required', 'true');
        }
    });

    // --- 3. FUNCIONALIDAD DEL BOTÓN COPIAR ---
    if (btnCopiar) {
        btnCopiar.addEventListener('click', function() {
            const numero = displayYapeNum.textContent;
            
            if (numero && numero !== "No registrado" && numero !== "...") {
                // API del portapapeles moderna
                navigator.clipboard.writeText(numero).then(() => {
                    // Mostrar mensaje de éxito
                    msgCopia.style.display = 'inline-block';
                    
                    // Ocultar mensaje después de 2 segundos
                    setTimeout(() => {
                        msgCopia.style.display = 'none';
                    }, 2000);
                }).catch(err => {
                    console.error('Error al copiar: ', err);
                    alert('No se pudo copiar el número automáticamente.');
                });
            }
        });
    }
});
</script>
<?php include 'includes/footer.php'; ?>