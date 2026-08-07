<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificación de autenticación y roles
if (!isset($_SESSION['user']) && !isset($_SESSION['cargo'])) {
    header("Location: login.php");
    exit();
}

// Permitir acceso a Ventas, Administrador y Admin
$cargos_autorizados = ['ventas']; 
$cargo_usuario = strtolower(trim($_SESSION['cargo'] ?? $_SESSION['rol'] ?? ''));

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

// 2. Verificar datos enviados vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['items']) && is_array($_POST['items'])) {
    
    require_once 'conexion.php';

    try {
        // Iniciar transacción SQL para garantizar atomicidad en lote
        $conexion->begin_transaction();

        $sql = "UPDATE disponibilidad_inventario 
                SET cantidad = ?, 
                    dias_venta = ?, 
                    pen_liberar = ?, 
                    fecha_actualizacion = NOW() 
                WHERE id = ?";

        $stmt = $conexion->prepare($sql);

        if (!$stmt) {
            throw new Exception("Error al preparar la consulta.");
        }

        foreach ($_POST['items'] as $item) {
            $id          = intval($item['id'] ?? 0);
            $cantidad    = max(0, intval($item['cantidad'] ?? 0));
            $dias_venta  = max(0, floatval($item['dias_venta'] ?? 0));
            $pen_liberar = max(0, intval($item['pen_liberar'] ?? 0));

            if ($id > 0) {
                // Tipo de parámetros: i = entero, d = decimal, i = entero, i = entero
                $stmt->bind_param("idii", $cantidad, $dias_venta, $pen_liberar, $id);
                $stmt->execute();
            }
        }

        $stmt->close();
        
        // Confirmar todos los cambios si el bucle finalizó con éxito
        $conexion->commit();
        $conexion->close();

        header("Location: disponibilidad.php?status=success");
        exit();

    } catch (Exception $e) {
        // Revertir cambios si hubo algún error en la base de datos
        if (isset($conexion) && $conexion->connect_errno === 0) {
            $conexion->rollback();
            $conexion->close();
        }
        
        header("Location: disponibilidad.php?status=error");
        exit();
    }

} else {
    header("Location: disponibilidad.php");
    exit();
}
?>