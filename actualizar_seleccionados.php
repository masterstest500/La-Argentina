<?php
session_start();

// 1. Verificación de autenticación y roles
if (!isset($_SESSION['user']) && !isset($_SESSION['cargo'])) {
    header("Location: login.php");
    exit();
}

$cargos_autorizados = ['preventista', 'administrador']; 
$cargo_usuario = strtolower($_SESSION['cargo'] ?? $_SESSION['rol'] ?? '');

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

// 2. Verificar que se hayan enviado datos vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['items']) && is_array($_POST['items'])) {
    
    require_once 'conexion.php';

    // 3. Preparar la consulta SQL (se actualiza también la fecha del registro)
    $sql = "UPDATE disponibilidad_inventario 
            SET cantidad = ?, 
                dias_venta = ?, 
                pen_liberar = ?, 
                fecha_actualizacion = NOW() 
            WHERE id = ?";

    if ($stmt = $conexion->prepare($sql)) {

        // Recorrer los productos modificados desde el modal
        foreach ($_POST['items'] as $item) {
            $id = intval($item['id']);
            $cantidad = intval($item['cantidad']);
            $dias_venta = floatval($item['dias_venta']);
            $pen_liberar = intval($item['pen_liberar']);

            // Asignar tipos de datos: i = entero, d = decimal
            $stmt->bind_param("idii", $cantidad, $dias_venta, $pen_liberar, $id);
            $stmt->execute();
        }

        $stmt->close();
        $conexion->close();

        // Redireccionar con alerta de éxito
        header("Location: neveras.php?status=success");
        exit();

    } else {
        // En caso de fallo en la preparación de la consulta
        header("Location: neveras.php?status=error");
        exit();
    }

} else {
    // Si se intenta ingresar al archivo sin enviar datos
    header("Location: neveras.php");
    exit();
}