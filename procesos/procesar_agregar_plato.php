<?php
session_start();
require_once '../includes/conexion.php';

// Verificamos que el usuario haya iniciado sesión
if (!isset($_SESSION['restaurante_id'])) {
    // Si no ha iniciado sesión, no tiene permiso para estar aquí
    header('Location: ../login_restaurante.php');
    exit();
}

// Verificamos que los datos lleguen por el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Recogemos los datos del formulario
    $nombre_plato = trim($_POST['nombre_plato']);
    $precio = trim($_POST['precio']);
    $descripcion = trim($_POST['descripcion']);
    // Obtenemos el ID del restaurante desde la sesión (¡muy importante!)
    $id_restaurante = $_SESSION['restaurante_id'];
    
    // --- Lógica para subir la imagen (opcional) ---
    $nombre_imagen = 'default.jpg'; // Imagen por defecto si no se sube una nueva
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $directorio_destino = "../assets/img/platos/";
        // Crear un nombre de archivo único para evitar sobreescribir imágenes
        $nombre_imagen = uniqid('plato_') . '_' . basename($_FILES['foto']['name']);
        $ruta_completa = $directorio_destino . $nombre_imagen;
        
        // Mover el archivo del directorio temporal al directorio final
        move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_completa);
    }
    // --- Fin de la lógica de imagen ---

    // Preparamos la consulta SQL para insertar el nuevo plato
    $sql = "INSERT INTO menu_platos (id_restaurante, nombre_plato, descripcion, precio, foto_url) VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // Vinculamos los parámetros
    $stmt->bind_param("issss", $id_restaurante, $nombre_plato, $descripcion, $precio, $nombre_imagen);
    
    // Ejecutamos la consulta
    if ($stmt->execute()) {
        // Si el plato se guarda correctamente, redirigimos al dashboard
        header("Location: ../restaurante/dashboard.php?status=plato_agregado");
        exit();
    } else {
        // Si hay un error, mostramos un mensaje
        die("Error al guardar el plato: " . $stmt->error);
    }
    
    $stmt->close();
    $conn->close();
}
?>