<?php
// activar_cuenta.php
require_once 'includes/conexion.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // 1. Buscar si existe ese token
    $sql = "SELECT id FROM usuarios_clientes WHERE token_verificacion = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        $id_usuario = $usuario['id'];

        // 2. Activar la cuenta y borrar el token (para que no se use de nuevo)
        $update = "UPDATE usuarios_clientes SET cuenta_confirmada = 1, token_verificacion = NULL WHERE id = ?";
        $stmt_up = $conn->prepare($update);
        $stmt_up->bind_param("i", $id_usuario);
        
        if ($stmt_up->execute()) {
            // ¡Éxito!
            header("Location: login_cliente.php?status=cuenta_activada");
        } else {
            echo "Error al activar la cuenta.";
        }
    } else {
        echo "Token inválido o cuenta ya activada.";
    }
} else {
    header("Location: index.php");
}
?>