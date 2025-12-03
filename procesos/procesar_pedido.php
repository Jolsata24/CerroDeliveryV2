<?php
session_start();
require_once '../includes/conexion.php';
require_once '../includes/funciones.php'; 

if (!isset($_SESSION['cliente_id'])) { die("Acceso no autorizado."); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cliente = $_SESSION['cliente_id'];
    $direccion_pedido = trim($_POST['direccion_pedido']);
    $id_restaurante = $_POST['id_restaurante'];
    $carrito_json = $_POST['carrito_data'];
    $latitud_cliente = !empty($_POST['latitud']) ? $_POST['latitud'] : null;
    $longitud_cliente = !empty($_POST['longitud']) ? $_POST['longitud'] : null;
    $metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';
    
    // --- LÓGICA DE SUBIDA DE IMAGEN YAPE ---
    $nombre_comprobante = null;
    if ($metodo_pago == 'yape' && isset($_FILES['comprobante_yape']) && $_FILES['comprobante_yape']['error'] == 0) {
        $directorio = "../assets/img/comprobantes/";
        if (!is_dir($directorio)) mkdir($directorio, 0755, true);
        
        $ext = pathinfo($_FILES['comprobante_yape']['name'], PATHINFO_EXTENSION);
        $nombre_unico = "pago_" . time() . "_" . $id_cliente . "." . $ext;
        
        if (move_uploaded_file($_FILES['comprobante_yape']['tmp_name'], $directorio . $nombre_unico)) {
            $nombre_comprobante = $nombre_unico;
        } else {
            die("Error al subir el comprobante de pago.");
        }
    }
    // ---------------------------------------

    $carrito = json_decode($carrito_json, true);
    if (empty($carrito) || empty($id_restaurante)) { die("Error: Datos inválidos."); }

    // Cálculo de envío (Simplificado basado en tu código previo)
    $costo_envio = 5.00;
    if ($latitud_cliente && $longitud_cliente) {
        $sql_r = "SELECT latitud, longitud FROM restaurantes WHERE id = ?";
        $stmt_r = $conn->prepare($sql_r);
        $stmt_r->bind_param("i", $id_restaurante);
        $stmt_r->execute();
        $res_r = $stmt_r->get_result()->fetch_assoc();
        if ($res_r && $res_r['latitud']) {
            $distancia = calcularDistancia($res_r['latitud'], $res_r['longitud'], $latitud_cliente, $longitud_cliente);
            $costo_envio = calcularCostoEnvio($distancia);
        }
    }

    $monto_productos = 0;
    foreach ($carrito as $item) $monto_productos += $item['precio'] * $item['cantidad'];
    $monto_total = $monto_productos + $costo_envio;

    $conn->begin_transaction();
    try {
        // INSERT ACTUALIZADO: Agregamos metodo_pago y comprobante_pago
        $sql_pedido = "INSERT INTO pedidos (id_restaurante, id_cliente, direccion_pedido, latitud, longitud, monto_total, costo_envio, metodo_pago, comprobante_pago, estado_pedido) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente')";
        $stmt_pedido = $conn->prepare($sql_pedido);
        
        // i=int, s=string, d=double
        // id_rest(i), id_cli(i), dir(s), lat(d), lon(d), total(d), envio(d), metodo(s), comprobante(s)
        $stmt_pedido->bind_param("iisssddss", $id_restaurante, $id_cliente, $direccion_pedido, $latitud_cliente, $longitud_cliente, $monto_total, $costo_envio, $metodo_pago, $nombre_comprobante);
        
        $stmt_pedido->execute();
        $id_pedido = $conn->insert_id;

        $sql_detalle = "INSERT INTO detalle_pedidos (id_pedido, id_plato, nombre_plato, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)";
        $stmt_detalle = $conn->prepare($sql_detalle);

        foreach ($carrito as $item) {
            $stmt_detalle->bind_param("iisid", $id_pedido, $item['id'], $item['nombre'], $item['cantidad'], $item['precio']);
            $stmt_detalle->execute();
        }

        $conn->commit();
        header("Location: ../mis_pedidos.php?status=success");
        
    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}
?>