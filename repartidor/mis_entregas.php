<?php
session_start();
if (!isset($_SESSION['repartidor_id'])) {
    header('Location: ../login_repartidor.php');
    exit();
}
require_once '../includes/conexion.php';
$id_repartidor = $_SESSION['repartidor_id'];
$sql = "SELECT p.id, p.direccion_pedido, p.latitud, p.longitud, c.nombre as nombre_cliente, c.telefono as telefono_cliente, r.nombre_restaurante
        FROM pedidos p
        JOIN restaurantes r ON p.id_restaurante = r.id
        JOIN usuarios_clientes c ON p.id_cliente = c.id
        WHERE p.id_repartidor = ? AND p.estado_pedido = 'En camino'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_repartidor);
$stmt->execute();
$resultado_entregas = $stmt->get_result();
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Mis Entregas Activas</h2>
    <a href="dashboard.php" class="btn btn-secondary">Volver a Pedidos Disponibles</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if ($resultado_entregas->num_rows > 0): ?>
            <?php while ($entrega = $resultado_entregas->fetch_assoc()): ?>
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <strong>Entregar Pedido #<?php echo $entrega['id']; ?></strong>
                    </div>
                    <div class="card-body">
                        <p><strong>Restaurante:</strong> <?php echo htmlspecialchars($entrega['nombre_restaurante']); ?></p>
                        <p><strong>Cliente:</strong> <?php echo htmlspecialchars($entrega['nombre_cliente']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0"><strong>Teléfono Cliente:</strong> <?php echo htmlspecialchars($entrega['telefono_cliente']); ?></p>

                            <a href="https://wa.me/51<?php echo htmlspecialchars($entrega['telefono_cliente']); ?>?text=Hola, soy tu repartidor de CerroDelivery."
                                target="_blank"
                                class="btn btn-success btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                    <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z" />
                                </svg>
                                Contactar
                            </a>
                        </div>
                        <p><strong>Dirección de Entrega:</strong> <?php echo htmlspecialchars($entrega['direccion_pedido']); ?></p>
                        <hr>
                        <?php if (!empty($entrega['latitud']) && !empty($entrega['longitud'])): ?>
                            <a href="https://www.google.com/maps?q=<?php echo $entrega['latitud']; ?>,<?php echo $entrega['longitud']; ?>" target="_blank" class="btn btn-info w-100 mb-2">📍 Ver en Mapa (GPS)</a>
                        <?php endif; ?>
                        <form action="../procesos/completar_entrega.php" method="POST" class="mt-2">
                            <input type="hidden" name="id_pedido" value="<?php echo $entrega['id']; ?>">
                            <button type="submit" class="btn btn-primary w-100">Marcar como Entregado</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center text-muted">No tienes entregas activas en este momento.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Solo se activa si hay entregas activas
        <?php if ($resultado_entregas->num_rows > 0): ?>

            let watchId; // Para guardar la referencia del seguimiento

            function enviarUbicacion(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;

                console.log(`Enviando ubicación: ${lat}, ${lon}`);

                fetch('../procesos/actualizar_ubicacion_repartidor.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            lat: lat,
                            lon: lon
                        })
                    })
                    .then(response => response.json())
                    .then(data => console.log('Respuesta del servidor:', data))
                    .catch(error => console.error('Error al enviar ubicación:', error));
            }

            function handleError(error) {
                console.warn(`ERROR(${error.code}): ${error.message}`);
            }

            // Usamos watchPosition para obtener actualizaciones automáticas si el repartidor se mueve
            if (navigator.geolocation) {
                watchId = navigator.geolocation.watchPosition(enviarUbicacion, handleError, {
                    enableHighAccuracy: true, // Pide la ubicación más precisa posible
                    timeout: 10000, // Tiempo máximo de espera
                    maximumAge: 0 // No usar una ubicación en caché
                });
            } else {
                alert("La geolocalización no es compatible con este navegador.");
            }

        <?php endif; ?>
    });
</script>

<?php
$stmt->close();
$conn->close();
include '../includes/footer.php';
?>