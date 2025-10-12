<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['cliente_id'])) {
    die("Acceso no autorizado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recoger datos (incluyendo los nuevos campos)
    $id_cliente = $_SESSION['cliente_id'];
    $direccion_pedido = trim($_POST['direccion_pedido']);
    $id_restaurante = $_POST['id_restaurante'];
    $carrito_json = $_POST['carrito_data'];
    $carrito = json_decode($carrito_json, true);

    // NUEVO: Recoger latitud y longitud. Usar null si están vacíos.
    $latitud = !empty($_POST['latitud']) ? $_POST['latitud'] : null;
    $longitud = !empty($_POST['longitud']) ? $_POST['longitud'] : null;

    if (empty($carrito) || empty($id_restaurante)) {
        die("Error: El carrito o el restaurante no son válidos.");
    }
    
    // ... (El código para calcular el monto_total sigue igual) ...
    $monto_total = 0;
    foreach ($carrito as $item) {
        $monto_total += $item['precio'] * $item['cantidad'];
    }

    $conn->begin_transaction();
    try {
        // 4. Actualizar la consulta INSERT para incluir las nuevas columnas
        $sql_pedido = "INSERT INTO pedidos (id_restaurante, id_cliente, direccion_pedido, latitud, longitud, monto_total) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_pedido = $conn->prepare($sql_pedido);
        // 'iisssd' son los tipos: integer, integer, string, string (para lat/lon), double
        $stmt_pedido->bind_param("iisssd", $id_restaurante, $id_cliente, $direccion_pedido, $latitud, $longitud, $monto_total);
        $stmt_pedido->execute();
        
        $id_pedido = $conn->insert_id;

        // ... (resto del código para insertar en detalle_pedidos y commit sin cambios) ...

    } catch (Exception $e) {
        $conn->rollback();
        die("Error al procesar el pedido: " . $e->getMessage());
    }

    // ... (cierre de conexiones) ...
}
?>