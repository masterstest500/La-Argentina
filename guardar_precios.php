<?php
session_start();
include('conexion.php');

// 1. Validar permisos y método POST
if (!isset($_SESSION['user']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?error=acceso_no_autorizado");
    exit();
}

// 2. Verificar datos esperados de la grilla
if (isset($_POST['codigo_producto']) && isset($_POST['nuevo_precio'])) {
    
    $codigos = $_POST['codigo_producto'];
    $precios = $_POST['nuevo_precio'];

    // 3. Sentencia UPSERT limpia
    $sql = "
        INSERT INTO productos (codigo, sabor, precio)
        SELECT di.codigo, di.producto, ?
        FROM disponibilidad_inventario di
        WHERE di.codigo = ?
        ON DUPLICATE KEY UPDATE precio = VALUES(precio)
    ";

    $stmt = $conexion->prepare($sql);

    if ($stmt) {
        for ($i = 0; $i < count($codigos); $i++) {
            $codigo = trim($codigos[$i]);
            $nuevo_precio = floatval($precios[$i]);

            if (!empty($codigo) && $nuevo_precio >= 0) {
                // "d" para precio (decimal), "s" para código (string)
                $stmt->bind_param("ds", $nuevo_precio, $codigo);
                $stmt->execute();
            }
        }

        $stmt->close();
        
        // 4. Redireccionar con indicador de éxito
        header("Location: precio_tasa.php?status=precios_actualizados");
        exit();
    } else {
        echo "Error en la preparación de la consulta: " . $conexion->error;
    }
} else {
    header("Location: precio_tasa.php");
    exit();
}
?>