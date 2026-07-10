<?php
session_start();

// 🛡️ Filtro de seguridad: Solo Admin o Ventas pueden registrar clientes
$cargoUsuario = isset ($_SESSION['cargo']) ? strtolower($_SESSION['cargo']) : '';

if (!isset($_SESSION['user']) || !in_array($cargoUsuario, ['administrador', 'ventas', 'preventista'])) {
    header('Location: index.php?error=acceso_denegado');
    exit();
}

// 🔌 Conexión a la base de datos
// (Asegúrate de que 'conexion.php' sea el nombre exacto de tu archivo de conexión)
require_once 'conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 🧹 Recibir y limpiar datos para evitar espacios vacíos accidentales
    $nombre_negocio   = trim($_POST['nombre']);      // Viene del input name="nombre"
    $rif              = trim($_POST['rif']);         // Viene del input name="rif"
    $direccion_fiscal = trim($_POST['direccion']);   // Viene del input name="direccion"
    $latitud          = $_POST['latitud'];           // Capturado por Leaflet.js
    $longitud         = $_POST['longitud'];          // Capturado por Leaflet.js

    // 🛑 Validación del lado del servidor: Que no falte nada importante
    if (empty($nombre_negocio) || empty($rif) || empty($direccion_fiscal) || empty($latitud) || empty($longitud)) {
        echo "<script>alert('Error: Todos los campos, incluyendo la ubicación en el mapa, son totalmente obligatorios.'); window.history.back();</script>";
        exit();
    }

    // 📝 Consulta SQL con las columnas exactas de tu phpMyAdmin (rif, nombre_negocio, direccion_fiscal, latitud, longitud)
    $sql = "INSERT INTO clientes (rif, nombre_negocio, direccion_fiscal, latitud, longitud) VALUES (?, ?, ?, ?, ?)";
    
    // ⚔️ Prepared Statement para evitar inyecciones SQL
    if ($stmt = $conexion->prepare($sql)) {
        
        // "sssdd" significa: String, String, String, Double (decimal), Double (decimal)
        $stmt->bind_param("sssdd", $rif, $nombre_negocio, $direccion_fiscal, $latitud, $longitud);
        
        if ($stmt->execute()) {
            // 🎉 ¡Éxito absoluto! Alerta estética y redirección limpia para evitar duplicados al pulsar F5
            echo "<script>alert('¡Espectacular! Cliente registrado y geolocalizado correctamente en Maracaibo.'); window.location.href = 'clientes.php';</script>";
        } else {
            echo "<script>alert('Error al insertar en la base de datos: " . $stmt->error . "'); window.history.back();</script>";
        }
        
        $stmt->close();
    } else {
        echo "<script>alert('Error crítico al preparar la consulta: " . $conexion->error . "'); window.history.back();</script>";
    }
}

$conexion->close();
?>