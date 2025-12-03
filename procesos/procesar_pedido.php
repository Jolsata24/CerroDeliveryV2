<?php
session_start();
require_once '../includes/conexion.php';
require_once '../includes/funciones.php'; 

if (!isset($_SESSION['cliente_id'])) {
    die("Acceso no autorizado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recoger todos los datos del formulario PRIMERO
    $id_cliente = $_SESSION['cliente_id'];
    $direccion_pedido = trim($_POST['direccion_pedido']);
    $id_restaurante = $_POST['id_restaurante'];
    $carrito_json = $_POST['carrito_data'];
    $latitud_cliente = !empty($_POST['latitud']) ? $_POST['latitud'] : null;
    $longitud_cliente = !empty($_POST['longitud']) ? $_POST['longitud'] : null;
    $metodo_pago = $_POST['metodo_pago'] ?? 'efectivo'; // Default a efectivo si falla

    $carrito = json_decode($carrito_json, true);

    if (empty($carrito) || empty($id_restaurante)) {
        die("Error: El carrito o el restaurante no son válidos.");
    }

    // --- CÁLCULO DE COSTO DE ENVÍO (AHORA SÍ FUNCIONA) ---
    $costo_envio = 0;
    
    // Solo calculamos si tenemos coordenadas válidas del cliente
    if ($latitud_cliente && $longitud_cliente) {
        // Obtener coordenadas del restaurante de la BD
        $sql_r = "SELECT latitud, longitud FROM restaurantes WHERE id = ?";
        $stmt_r = $conn->prepare($sql_r);
        $stmt_r->bind_param("i", $id_restaurante);
        $stmt_r->execute();
        $res_r = $stmt_r->get_result()->fetch_assoc();
        
        // Solo si el restaurante también tiene coordenadas
        if ($res_r && $res_r['latitud'] && $res_r['longitud']) {
            $rest_lat = $res_r['latitud'];
            $rest_lon = $res_r['longitud'];
            
            // Calculamos distancia y costo
            $distancia = calcularDistancia($rest_lat, $rest_lon, $latitud_cliente, $longitud_cliente);
            $costo_envio = calcularCostoEnvio($distancia);
        } else {
            // Si el restaurante no tiene mapa, cobramos tarifa base por defecto
            $costo_envio = 5.00; 
        }
        $stmt_r->close();
    } else {
        $costo_envio = 5.00; // Tarifa base si no hay GPS del cliente
    }

    // 2. Calcular el monto de los productos
    $monto_productos = 0;
    foreach ($carrito as $item) {
        $monto_productos += $item['precio'] * $item['cantidad'];
    }

    // 3. Monto FINAL (Productos + Envío)
    $monto_total = $monto_productos + $costo_envio;

    // 4. Iniciar transacción
    $conn->begin_transaction();
    $id_pedido = 0;

    try {
        // INSERT ACTUALIZADO: Incluye costo_envio y metodo_pago si tienes la columna
        // Asumiendo que agregaste 'costo_envio' a la tabla como te indiqué antes.
        $sql_pedido = "INSERT INTO pedidos (id_restaurante, id_cliente, direccion_pedido, latitud, longitud, monto_total, costo_envio) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_pedido = $conn->prepare($sql_pedido);
        
        // Tipos: i=int, s=string, d=double. 
        // Orden: id_rest, id_cli, dir, lat, lon, total, envio
        $stmt_pedido->bind_param("iisssdd", $id_restaurante, $id_cliente, $direccion_pedido, $latitud_cliente, $longitud_cliente, $monto_total, $costo_envio);
        
        $stmt_pedido->execute();
        $id_pedido = $conn->insert_id;

        // 5. Insertar detalles
        $sql_detalle = "INSERT INTO detalle_pedidos (id_pedido, id_plato, nombre_plato, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)";
        $stmt_detalle = $conn->prepare($sql_detalle);

        foreach ($carrito as $item) {
            $stmt_detalle->bind_param("iisid", $id_pedido, $item['id'], $item['nombre'], $item['cantidad'], $item['precio']);
            $stmt_detalle->execute();
        }

        $conn->commit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Error al procesar el pedido: " . $e->getMessage());
    }

    // 7. Notificación por Email (Tu código existente, mantenlo igual)
    // ... [Aquí va tu bloque de mail() que ya tenías] ...
    
    // Cerrar y redirigir
    if (isset($stmt_pedido)) $stmt_pedido->close();
    if (isset($stmt_detalle)) $stmt_detalle->close();
    $conn->close();

    header("Location: ../mis_pedidos.php?status=success");
    exit();
}
?>