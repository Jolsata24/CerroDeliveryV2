<?php
// procesos/procesar_login_cliente.php
session_start();
require_once '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Agregamos 'cuenta_confirmada' al SELECT
    $sql = "SELECT id, nombre, password, cuenta_confirmada FROM usuarios_clientes WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $cliente = $result->fetch_assoc();
        
        if (password_verify($password, $cliente['password'])) {
            
            // --- NUEVA VERIFICACIÓN ---
            if ($cliente['cuenta_confirmada'] == 0) {
                header("Location: ../login_cliente.php?error=Tu cuenta no ha sido activada. Revisa tu correo.");
                exit();
            }
            // --------------------------

            $_SESSION['cliente_id'] = $cliente['id'];
            $_SESSION['cliente_nombre'] = $cliente['nombre'];
            
            header("Location: ../index.php");
            exit();
        }
    }

    header("Location: ../login_cliente.php?error=1");
    exit();
}
?>