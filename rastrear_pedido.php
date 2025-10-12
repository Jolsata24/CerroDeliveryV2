<?php
session_start();
if (!isset($_SESSION['cliente_id']) || !isset($_GET['id_pedido'])) {
    header('Location: login_cliente.php');
    exit();
}
require_once 'includes/conexion.php';

$id_pedido = $_GET['id_pedido'];
$id_cliente_sesion = $_SESSION['cliente_id'];

// Consulta para obtener los datos del pedido, incluyendo la ubicación del cliente (si existe)
// y el ID del repartidor asignado.
$sql = "SELECT p.latitud as latitud_cliente, p.longitud as longitud_cliente, p.id_repartidor
        FROM pedidos p
        WHERE p.id = ? AND p.id_cliente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_pedido, $id_cliente_sesion);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    die("Pedido no encontrado o no te pertenece.");
}

$pedido = $resultado->fetch_assoc();
$id_repartidor = $pedido['id_repartidor'];

// Si no hay repartidor asignado, no podemos rastrear.
if (is_null($id_repartidor)) {
    die("Aún no se ha asignado un repartidor a tu pedido.");
}

include 'includes/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<h1 class="mb-4">Rastreando tu Pedido #<?php echo $id_pedido; ?></h1>
<div id="mapa" style="height: 500px; width: 100%;" class="card"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Coordenadas del cliente (si las proporcionó)
    const latCliente = <?php echo $pedido['latitud_cliente'] ?? 'null'; ?>;
    const lonCliente = <?php echo $pedido['longitud_cliente'] ?? 'null'; ?>;
    const idRepartidor = <?php echo $id_repartidor; ?>;

    // Centrar el mapa en la ubicación del cliente o en una ubicación por defecto
    const centroMapa = (latCliente && lonCliente) ? [latCliente, lonCliente] : [-12.046374, -77.042793]; // Coordenadas de Lima por defecto

    const mapa = L.map('mapa').setView(centroMapa, 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(mapa);

    // Marcador para el cliente (tu casa)
    if (latCliente && lonCliente) {
        L.marker([latCliente, lonCliente]).addTo(mapa).bindPopup('<b>Tu ubicación de entrega</b>');
    }

    // Marcador para el repartidor (este se moverá)
    let marcadorRepartidor = L.marker(centroMapa, { 
        icon: L.icon({ // Icono personalizado para el repartidor
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41]
        })
    }).addTo(mapa).bindPopup('<b>Repartidor</b>');

    // Función para obtener y actualizar la ubicación del repartidor
    async function actualizarUbicacion() {
        try {
            const response = await fetch(`procesos/obtener_ubicacion_repartidor.php?id_repartidor=${idRepartidor}`);
            const data = await response.json();

            if (data.status === 'success') {
                const nuevaPosicion = [data.latitud, data.longitud];
                marcadorRepartidor.setLatLng(nuevaPosicion);
                // Opcional: Centrar el mapa en la nueva posición del repartidor
                // mapa.setView(nuevaPosicion); 
            } else {
                console.warn(data.message);
            }
        } catch (error) {
            console.error("Error al obtener la ubicación:", error);
        }
    }
    
    // Llamar a la función cada 5 segundos
    setInterval(actualizarUbicacion, 5000);
    actualizarUbicacion(); // Primera llamada inmediata
});
</script>

<?php
$stmt->close();
$conn->close();
include 'includes/footer.php';
?>