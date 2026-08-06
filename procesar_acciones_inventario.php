<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'conexion.php';

// Verificación de seguridad (con trim() para ignorar espacios invisibles)
$cargos_autorizados = ['ventas', 'administrador', 'admin']; 
$cargo_usuario = strtolower(trim($_SESSION['cargo'] ?? $_SESSION['rol'] ?? ''));

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    
    $accion = $_POST['accion'];

    if ($accion === 'eliminar_seleccionados') {
        
        if (!empty($_POST['ids']) && is_array($_POST['ids'])) {
            // Sanitizar los IDs recibidos para evitar Inyección SQL
            $ids_limpios = array_map('intval', $_POST['ids']);
            $ids_string = implode(',', $ids_limpios);
            
            // Ejecutar el borrado masivo de los seleccionados
            $sql = "DELETE FROM disponibilidad_inventario WHERE id IN ($ids_string)";
            $conexion->query($sql);
            
            header("Location: disponibilidad.php?status=deleted_selected");
            exit();
        } else {
            // Si por alguna razón llegó vacío, se devuelve sin hacer nada
            header("Location: disponibilidad.php");
            exit();
        }
    }

} else {
    // Si acceden directamente a este archivo o con una acción no válida
    header("Location: disponibilidad.php");
    exit();
}
?>