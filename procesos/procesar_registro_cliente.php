<?php
require_once '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recoger y limpiar datos
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $telefono = trim($_POST['telefono']); // Ahora siempre tendrá un valor

    // Validar que los campos no estén vacíos
    if (empty($nombre) || empty($email) || empty($password) || empty($telefono)) {
        die("Error: Todos los campos, incluido el teléfono, son obligatorios.");
    }
    
    // Encriptar la contraseña (¡siempre!)
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Preparar la consulta SQL para evitar inyección
    $sql = "INSERT INTO usuarios_clientes (nombre, email, password, telefono) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nombre, $email, $password_hash, $telefono);
    
    // Ejecutar y manejar el resultado
    if ($stmt->execute()) {
        header("Location: ../login_cliente.php?status=registrado");
        exit();
    } else {
        if ($conn->errno == 1062) {
            die("Error: Este correo electrónico ya está en uso.");
        } else {
            die("Error al crear la cuenta: " . $stmt->error);
        }
    }
    
    $stmt->close();
    $conn->close();
}
?>