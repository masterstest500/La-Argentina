<?php
// Credenciales de conexión por defecto en XAMPP
$host = "localhost";
$user = "root";
$password = "";
$database = "heladeria_bbdd";

// Conectar al servidor de MySQL
$conexion = mysqli_connect($host, $user, $password, $database);

// Validar si la conexión falló
if (!$conexion) {
    die("❌ Error crítico: No se pudo conectar a la base de datos. Motivo: " . mysqli_connect_error());
}

// Configurar caracteres en UTF-8 para admitir eñes, acentos y caracteres especiales
mysqli_set_charset($conexion, "utf8mb4");

// Mensaje temporal de éxito para nuestra prueba de fuego
// (Lo borraremos o comentaremos una vez sepamos que funciona)
// echo "🔌 ¡Conexión exitosa y segura a la base de datos MySQL!";
?>