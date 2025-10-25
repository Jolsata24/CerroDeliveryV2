<?php
// =================================================================
// ARCHIVO DE CONEXIÓN A LA BASE DE DATOS PARA CERRODELIVERY
// =================================================================

// -----------------------------------------------------------------
// 1. DATOS DE CONFIGURACIÓN (PARA TU SERVIDOR LOCAL XAMPP)
// -----------------------------------------------------------------
$servername = "162.241.60.127";        // El servidor donde está la base de datos
$username = "herework_jolsata24";               // El usuario de la base de datos (por defecto en XAMPP)
$password = "polloasado500";                   // La contraseña del usuario (vacía por defecto en XAMPP)
$dbname = "herework_cerrodelivery";     // El nombre de la base de datos que creaste

// -----------------------------------------------------------------
// 2. CREAR LA CONEXIÓN USANDO MySQLi (Método Orientado a Objetos)
// -----------------------------------------------------------------
$conn = new mysqli($servername, $username, $password, $dbname);

// -----------------------------------------------------------------
// 3. VERIFICAR LA CONEXIÓN
// Si hay un error, el script se detendrá y mostrará un mensaje.
// -----------------------------------------------------------------
if ($conn->connect_error) {
    die("❌ Error de Conexión: " . $conn->connect_error);
}

// -----------------------------------------------------------------
// 4. ESTABLECER EL JUEGO DE CARACTERES A UTF-8
// Esto es MUY IMPORTANTE para que se muestren correctamente las
// tildes (á, é, í), eñes (ñ) y otros caracteres especiales.
// -----------------------------------------------------------------
$conn->set_charset("utf8mb4");

// Si el script llega hasta aquí, significa que la conexión fue exitosa.
// No se muestra ningún mensaje de éxito porque este archivo será incluido
// de forma silenciosa en otros scripts.

?>