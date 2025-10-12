<?php
require_once '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recoger y limpiar datos
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $telefono = trim($_POST['telefono']) ?? null; // Asignar null si está vacío

    if (empty($nombre) || empty($email) || empty($password)) {
        die("Error: Nombre, email y contraseña son obligatorios.");
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