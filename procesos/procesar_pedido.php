<?php
session_start();
require_once '../includes/conexion.php';

// Verificamos que el cliente que procesa el pedido ha iniciado sesión
if (!isset($_SESSION['cliente_id'])) {
    die("Acceso no autorizado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recoger datos del cliente DESDE LA SESIÓN
    $id_cliente = $_SESSION['cliente_id'];
    $direccion_pedido = trim($_POST['direccion_pedido']);

    // 2. Recoger datos del carrito
    $carrito_json = $_POST['carrito_data'];
    $carrito = json_decode($carrito_json, true);

    if (empty($carrito)) { die("Error: El carrito está vacío."); }

    // ... (El código para obtener el id_restaurante y calcular el monto_total sigue igual) ...
    $primer_plato_id = $carrito[0]['id'];
    // ... (resto del código igual hasta el INSERT)

    // --- INICIO DE LA TRANSACCIÓN ---
    $conn->begin_transaction();
    try {
        // 4. Insertar en la tabla `pedidos` con la nueva estructura
        $sql_pedido = "INSERT INTO pedidos (id_restaurante, id_cliente, direccion_pedido, monto_total) VALUES (?, ?, ?, ?)";
        $stmt_pedido = $conn->prepare($sql_pedido);
        $stmt_pedido->bind_param("iisd", $id_restaurante, $id_cliente, $direccion_pedido, $monto_total);
        $stmt_pedido->execute();
        
        $id_pedido = $conn->insert_id;

        // ... (El resto del código para insertar en detalle_pedidos sigue exactamente igual) ...

        $conn->commit();

        echo "<h1>¡Pedido realizado con éxito!</h1>";
        // ... (El mensaje de éxito puede seguir igual) ...

    } catch (Exception $e) {
        $conn->rollback();
        die("Error al procesar el pedido: " . $e->getMessage());
    }
    // --- FIN DE LA TRANSACCIÓN ---
}
?>