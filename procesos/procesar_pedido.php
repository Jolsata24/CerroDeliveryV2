<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['cliente_id'])) {
    die("Acceso no autorizado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recoger datos
    $id_cliente = $_SESSION['cliente_id'];
    $direccion_pedido = trim($_POST['direccion_pedido']);
    $id_restaurante = $_POST['id_restaurante'];
    $carrito_json = $_POST['carrito_data'];
    $carrito = json_decode($carrito_json, true);

    $latitud = !empty($_POST['latitud']) ? $_POST['latitud'] : null;
    $longitud = !empty($_POST['longitud']) ? $_POST['longitud'] : null;

    if (empty($carrito) || empty($id_restaurante)) {
        die("Error: El carrito o el restaurante no son válidos.");
    }
    
    // 2. Calcular el monto total
    $monto_total = 0;
    foreach ($carrito as $item) {
        $monto_total += $item['precio'] * $item['cantidad'];
    }

    // 3. Iniciar la transacción
    $conn->begin_transaction();

    try {
        // 4. Insertar el pedido principal en la tabla `pedidos`
        $sql_pedido = "INSERT INTO pedidos (id_restaurante, id_cliente, direccion_pedido, latitud, longitud, monto_total) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_pedido = $conn->prepare($sql_pedido);
        $stmt_pedido->bind_param("iisssd", $id_restaurante, $id_cliente, $direccion_pedido, $latitud, $longitud, $monto_total);
        $stmt_pedido->execute();
        
        // Obtener el ID del pedido que acabamos de crear
        $id_pedido = $conn->insert_id;

        // 5. ¡NUEVO! Insertar cada plato en la tabla `detalle_pedidos`
        $sql_detalle = "INSERT INTO detalle_pedidos (id_pedido, id_plato, nombre_plato, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)";
        $stmt_detalle = $conn->prepare($sql_detalle);

        foreach ($carrito as $item) {
            $stmt_detalle->bind_param("iisid", $id_pedido, $item['id'], $item['nombre'], $item['cantidad'], $item['precio']);
            $stmt_detalle->execute();
        }
        
        // 6. ¡CRUCIAL! Confirmar la transacción para guardar todos los cambios
        $conn->commit();

    } catch (Exception $e) {
        // Si algo falla, revertir todos los cambios
        $conn->rollback();
        die("Error al procesar el pedido: " . $e->getMessage());
    }

    // 7. Limpiar el carrito de la sesión y redirigir
    // unset($_SESSION['carritoData']); // Descomenta si usas sesión de PHP para el carrito
    
    // Cierre de sentencias preparadas
    $stmt_pedido->close();
    $stmt_detalle->close();
    $conn->close();

    // Redirigir al usuario a su historial de pedidos
    header("Location: ../mis_pedidos.php?status=success");
    exit();
}
?>