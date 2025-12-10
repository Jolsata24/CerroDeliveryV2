<?php
// procesos/procesar_registro_cliente.php
require_once '../includes/conexion.php';

// Si tienes configurado PHPMailer, inclúyelo aquí.
// use PHPMailer\PHPMailer\PHPMailer; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $telefono = trim($_POST['telefono']);

    // ... (Tu validación de campos vacíos sigue igual) ...
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // 1. GENERAR TOKEN ÚNICO
    $token = bin2hex(random_bytes(32)); // Genera un código largo y aleatorio

    // 2. INSERTAR CON ESTADO 'NO CONFIRMADO' (0)
    $sql = "INSERT INTO usuarios_clientes (nombre, email, password, telefono, token_verificacion, cuenta_confirmada) VALUES (?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $nombre, $email, $password_hash, $telefono, $token);
    
    if ($stmt->execute()) {
        
        // 3. ENVIAR EL CORREO (Simulación)
        // En un entorno real, aquí usarías PHPMailer.
        $link_activacion = "http://localhost/cerrodeliveryv2/activar_cuenta.php?token=" . $token;
        
        $asunto = "Activa tu cuenta en CerroDelivery";
        $mensaje = "Hola $nombre, haz clic aquí para activar tu cuenta: $link_activacion";
        
        // mail($email, $asunto, $mensaje); // La función mail() de PHP suele ir a SPAM.
        
        // POR AHORA: Redirigimos a una página que le pida revisar su correo
        header("Location: ../login_cliente.php?msg=Revisa tu correo para activar tu cuenta (Link simulado: $link_activacion)");
        exit();

    } else {
        // Error 1062 = Dato duplicado (Email ya existe)
        if ($conn->errno == 1062) {
            // CAMBIO: En lugar de die(), redirigimos con ?error=existe
            header("Location: ../registro_cliente.php?error=existe");
            exit();
        } else {
            die("Error técnico al registrar: " . $stmt->error);
        }
    }
    
    $stmt->close();
    $conn->close();
}
?>