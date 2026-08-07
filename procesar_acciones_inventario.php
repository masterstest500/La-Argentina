<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'conexion.php';

// 1. Verificación de seguridad y roles (incluye administración)
$cargos_autorizados = ['ventas']; 
$cargo_usuario = strtolower(trim($_SESSION['cargo'] ?? $_SESSION['rol'] ?? ''));

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

// 2. Procesamiento de acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    
    $accion = $_POST['accion'];

    if ($accion === 'eliminar_seleccionados') {
        
        if (!empty($_POST['ids']) && is_array($_POST['ids'])) {
            // Sanitizar array de IDs
            $ids_limpios = array_map('intval', $_POST['ids']);
            
            // Generar marcadores dinamicos (?) para la consulta preparada
            $placeholders = implode(',', array_fill(0, count($ids_limpios), '?'));
            $tipos = str_repeat('i', count($ids_limpios));

            // Eliminar ÚNICAMENTE de disponibilidad_inventario (detalles_catalogo queda intacto)
            $sql = "DELETE FROM disponibilidad_inventario WHERE id IN ($placeholders)";
            
            if ($stmt = $conexion->prepare($sql)) {
                $stmt->bind_param($tipos, ...$ids_limpios);
                $stmt->execute();
                $stmt->close();
                $conexion->close();

                header("Location: disponibilidad.php?status=deleted_selected");
                exit();
            } else {
                header("Location: disponibilidad.php?status=error");
                exit();
            }

        } else {
            header("Location: disponibilidad.php");
            exit();
        }
    }

} else {
    header("Location: disponibilidad.php");
    exit();
}
?>