<?php
require_once '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Limpieza de datos
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $telefono = trim($_POST['telefono']);

    // Validar campos vacíos
    if (empty($nombre) || empty($email) || empty($password) || empty($telefono)) {
        header("Location: ../registro_cliente.php?error=campos_vacios");
        exit();
    }

    // 2. Encriptar contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // 3. Generar Token Único para validación
    $token = bin2hex(random_bytes(32)); 

    // 4. Preparar la inserción (cuenta_confirmada = 0)
    $sql = "INSERT INTO usuarios_clientes (nombre, email, password, telefono, token_verificacion, cuenta_confirmada) VALUES (?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nombre, $email, $password_hash, $telefono, $token);
    
    // --- AQUÍ ESTÁ LA SOLUCIÓN AL ERROR DE DUPLICADO ---
    try {
        $stmt->execute();
        
        // Si llegamos aquí, se guardó bien. Generamos el link de activación.
        // NOTA: Ajusta "cerrodeliveryv2" si tu carpeta se llama diferente.
        $link_activacion = "http://localhost/cerrodeliveryv2/activar_cuenta.php?token=" . $token;
        
        // Redirigimos al login con el mensaje (SIMULACIÓN DE CORREO)
        $mensaje = "Cuenta creada. Por favor verifica tu correo (Link simulado: $link_activacion)";
        header("Location: ../login_cliente.php?msg=" . urlencode($mensaje));
        exit();

    } catch (mysqli_sql_exception $e) {
        // Si el error es 1062, es un duplicado
        if ($conn->errno == 1062) {
            header("Location: ../registro_cliente.php?error=existe");
            exit();
        } else {
            // Otro error técnico
            die("Error en el sistema: " . $e->getMessage());
        }
    }
    // --------------------------------------------------
    
    $stmt->close();
    $conn->close();
}
?>