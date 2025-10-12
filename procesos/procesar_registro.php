<?php
require_once '../includes/conexion.php'; // Incluimos la conexión

// Verificamos que los datos lleguen por el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recogemos y limpiamos los datos
    $nombre = trim($_POST['nombre_restaurante']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validamos que los campos no estén vacíos
    if (empty($nombre) || empty($email) || empty($password)) {
        die("Error: Todos los campos son obligatorios.");
    }
    
    // Encriptamos la contraseña para máxima seguridad
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    
    // Preparamos la consulta SQL para evitar inyecciones SQL
    $sql = "INSERT INTO restaurantes (nombre_restaurante, email, password) VALUES (?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // Vinculamos los parámetros
    $stmt->bind_param("sss", $nombre, $email, $password_hash);
    
    // Ejecutamos la consulta
    if ($stmt->execute()) {
        // Si todo sale bien, lo redirigimos a la página de login con un mensaje de éxito
        header("Location: ../login_restaurante.php?status=success");
        exit();
    } else {
        // Si falla (por ejemplo, el email ya existe), mostramos un error
        // El código de error 1062 es para entradas duplicadas
        if ($conn->errno == 1062) {
            die("Error: El correo electrónico '$email' ya se encuentra registrado.");
        } else {
            die("Error al registrar el restaurante: " . $stmt->error);
        }
    }
    
    $stmt->close();
    $conn->close();
}
?>