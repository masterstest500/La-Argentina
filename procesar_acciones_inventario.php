<?php
session_start();
require_once 'conexion.php';

// Verificación de seguridad
$cargos_autorizados = ['preventista', 'administrador']; 
$cargo_usuario = strtolower($_SESSION['cargo'] ?? $_SESSION['rol'] ?? '');

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
            
            header("Location: neveras.php?status=deleted_selected");
            exit();
        } else {
            // Si por alguna razón llegó vacío, se devuelve sin hacer nada
            header("Location: neveras.php");
            exit();
        }
    }

} else {
    // Si acceden directamente a este archivo o con una acción no válida
    header("Location: neveras.php");
    exit();
}
?>