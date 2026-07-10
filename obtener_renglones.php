<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

// 🔒 CONTROL DE ACCESO INTELIGENTE
if (!isset($_SESSION['user']) || strtolower($_SESSION['cargo']) !== 'administrador') {
    echo json_encode(["status" => "error", "message" => "Acceso no autorizado."]);
    exit;
}

if (isset($_GET['id'])) {
    // Escapamos el ID para evitar Inyección SQL
    $id_pedido = mysqli_real_escape_string($conexion, $_GET['id']);

    // 🍦 CONSULTA AJUSTADA: Vincula producto_id con la columna 'sabor' de la tabla productos
    $sql = "SELECT d.cantidad, p.sabor AS producto, d.precio_unitario, d.subtotal 
            FROM detalle_pedidos d
            INNER JOIN productos p ON d.producto_id = p.id
            WHERE d.pedido_id = '$id_pedido'";

    $resultado = mysqli_query($conexion, $sql);
    
    if ($resultado) {
        $renglones = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $renglones[] = [
                "cantidad"        => intval($fila['cantidad']),
                "producto"        => $fila['producto'],
                "precio_unitario" => floatval($fila['precio_unitario']),
                "subtotal"        => floatval($fila['subtotal'])
            ];
        }
        echo json_encode(["status" => "success", "data" => $renglones]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error en la consulta: " . mysqli_error($conexion)]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Falta el identificador del pedido."]);
}
?>