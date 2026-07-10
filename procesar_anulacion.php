<?php
session_start();

// 1. CONTROL DE ACCESO INTELIGENTE
$usuario_autenticado = isset($_SESSION['user']);
$cargo_actual = isset($_SESSION['cargo']) ? strtolower($_SESSION['cargo']) : '';
$rol_actual = isset($_SESSION['rol']) ? strtolower($_SESSION['rol']) : '';

if (!$usuario_autenticado || ($cargo_actual !== 'administrador' && $rol_actual !== 'administrador')) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

// 2. VALIDAR PARÁMETRO
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: historial_pedidos.php?error=id_invalido");
    exit();
}

include("conexion.php");
$pedido_id = intval($_GET['id']);

// 3. INICIAR TRANSACCIÓN MULTI-TABLA
mysqli_begin_transaction($conexion);

try {
    // SEGURIDAD PRO: Verificar si ya estaba anulado antes para evitar doble carga de stock
    $query_verificar = "SELECT estado FROM pedidos WHERE id = $pedido_id";
    $res_verificar = mysqli_query($conexion, $query_verificar);
    $pedido_data = mysqli_fetch_assoc($res_verificar);

    if ($pedido_data && $pedido_data['estado'] === 'Anulado') {
        throw new Exception("Este pedido ya se encuentra anulado en el sistema.");
    }

    // A. Obtener los renglones de helados de este pedido
    $query_detalle = "SELECT producto_id, cantidad FROM detalle_pedidos WHERE pedido_id = $pedido_id";
    $result_detalle = mysqli_query($conexion, $query_detalle);

    if (!$result_detalle) {
        throw new Exception("Error al consultar el detalle del pedido.");
    }

    // B. Devolver los potes al stock de la cava
    while ($renglon = mysqli_fetch_assoc($result_detalle)) {
        $producto_id = $renglon['producto_id'];
        $cantidad    = $renglon['cantidad'];

        $query_restaurar_stock = "UPDATE productos SET stock_potes = stock_potes + $cantidad WHERE id = $producto_id";
        $update_stock = mysqli_query($conexion, $query_restaurar_stock);

        if (!$update_stock) {
            throw new Exception("Error al devolver stock del producto ID: $producto_id");
        }
    }

    // C. ACTUALIZACIÓN GERENCIAL: Cambiamos estado y totalizamos a cero
    $query_anular_maestro = "UPDATE pedidos SET estado = 'Anulado', total = 0.00 WHERE id = $pedido_id";
    $ejecutar_anulacion = mysqli_query($conexion, $query_anular_maestro);

    if (!$ejecutar_anulacion) {
        throw new Exception("Error al cambiar el estado del pedido maestro.");
    }

    // CONFIRMAMOS CAMBIOS
    mysqli_commit($conexion);
    
    header("Location: historial_pedidos.php?msg=anulado_exito");
    exit();

} catch (Exception $e) {
    mysqli_rollback($conexion);
    header("Location: historial_pedidos.php?error=falla_transaccion&detalle=" . urlencode($e->getMessage()));
    exit();
}
?>