<?php
session_start();
require_once '../includes/conexion.php';
require_once '../includes/funciones.php'; // Importar la fórmula
if (!isset($_SESSION['cliente_id'])) {
    die("Acceso no autorizado.");
}
// 1. Obtener coordenadas del restaurante de la BD
$sql_r = "SELECT latitud, longitud FROM restaurantes WHERE id = ?";
$stmt_r = $conn->prepare($sql_r);
$stmt_r->bind_param("i", $id_restaurante);
$stmt_r->execute();
$res_r = $stmt_r->get_result()->fetch_assoc();
$rest_lat = $res_r['latitud'];
$rest_lon = $res_r['longitud'];

// 2. Calcular distancia real
$distancia = calcularDistancia($rest_lat, $rest_lon, $latitud_cliente, $longitud_cliente);

// 3. Calcular costo de envío
$costo_envio = calcularCostoEnvio($distancia);

// 4. Sumar al total
$monto_final = $monto_productos + $costo_envio;

// 5. Insertar en BD (incluyendo el costo de envío)
$sql_pedido = "INSERT INTO pedidos (..., monto_total, costo_envio, ...) VALUES (?, ?, ...)";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recoger todos los datos del formulario
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

    // 3. Iniciar la transacción para la base de datos
    $conn->begin_transaction();
    $id_pedido = 0; // Inicializar variable

    try {
        // 4. Insertar el pedido principal
        $sql_pedido = "INSERT INTO pedidos (id_restaurante, id_cliente, direccion_pedido, latitud, longitud, monto_total) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_pedido = $conn->prepare($sql_pedido);
        $stmt_pedido->bind_param("iisssd", $id_restaurante, $id_cliente, $direccion_pedido, $latitud, $longitud, $monto_total);
        $stmt_pedido->execute();

        $id_pedido = $conn->insert_id; // Guardamos el ID del nuevo pedido

        // 5. Insertar los detalles del pedido (cada plato)
        $sql_detalle = "INSERT INTO detalle_pedidos (id_pedido, id_plato, nombre_plato, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)";
        $stmt_detalle = $conn->prepare($sql_detalle);

        foreach ($carrito as $item) {
            $stmt_detalle->bind_param("iisid", $id_pedido, $item['id'], $item['nombre'], $item['cantidad'], $item['precio']);
            $stmt_detalle->execute();
        }

        // 6. Confirmar la transacción para guardar permanentemente el pedido
        $conn->commit();

    } catch (Exception $e) {
        // Si algo falla al guardar en la BD, revertir todo y detener
        $conn->rollback();
        die("Error al procesar el pedido: " . $e->getMessage());
    }

    // 7. Si el pedido se guardó correctamente, intentar enviar la notificación por email.
    if ($id_pedido > 0) {
        try {
            // Obtener el email y el nombre del restaurante
            $sql_info = "SELECT email, nombre_restaurante FROM restaurantes WHERE id = ?";
            $stmt_info = $conn->prepare($sql_info);
            $stmt_info->bind_param("i", $id_restaurante);
            $stmt_info->execute();
            $restaurante_info = $stmt_info->get_result()->fetch_assoc();

            if ($restaurante_info) {
                $email_destino = $restaurante_info['email'];
                $nombre_restaurante = $restaurante_info['nombre_restaurante'];

                // =================================================================
                // CAMBIO 1: Reemplaza "tudominio.com" con tu dominio real
                // =================================================================
                // Cuando subas el proyecto a tu hosting, pon aquí tu dominio (ej: "cerrodelivery.com")
                // Para pruebas locales, puedes dejarlo como "localhost"
                $dominio_real = "localhost"; 
                
                $link_pedidos = "http://" . $dominio_real . "/restaurante/pedidos.php";

                // Prepara el correo
                $asunto = "¡Nuevo Pedido Recibido! - Pedido #" . $id_pedido;
                
                $mensaje = "Hola " . $nombre_restaurante . ",\n\n";
                $mensaje .= "Has recibido un nuevo pedido en CerroDelivery.\n\n";
                $mensaje .= "Detalles:\n";
                $mensaje .= " - Pedido #: " . $id_pedido . "\n";
                $mensaje .= " - Monto Total: S/ " . number_format($monto_total, 2) . "\n\n";
                $mensaje .= "Por favor, revisa los detalles en tu panel de control:\n";
                $mensaje .= $link_pedidos . "\n\n";
                $mensaje .= "Gracias,\nEl equipo de CerroDelivery";

                // =================================================================
                // CAMBIO 2: Usar un correo de tu propio dominio como remitente
                // =================================================================
                // Esto es crucial para que los correos no sean bloqueados o marcados como SPAM.
                $email_remitente = "notificaciones@" . $dominio_real;
                
                $cabeceras = 'From: ' . $email_remitente . "\r\n" .
                             'Reply-To: no-responder@' . $dominio_real . "\r\n" .
                             'X-Mailer: PHP/' . phpversion();

                // Envía el correo
                mail($email_destino, $asunto, $mensaje, $cabeceras);
            }
        } catch (Exception $e) {
            // Si el correo falla, no detenemos el proceso
            error_log("Fallo al enviar notificación por email: " . $e->getMessage());
        }
    }

    // 8. Cerrar todas las conexiones y redirigir al cliente
    if (isset($stmt_pedido)) $stmt_pedido->close();
    if (isset($stmt_detalle)) $stmt_detalle->close();
    if (isset($stmt_info)) $stmt_info->close();
    $conn->close();

    header("Location: ../mis_pedidos.php?status=success");
    exit();
}
?>