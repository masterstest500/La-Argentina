<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificación de autenticación y roles
$cargos_autorizados = ['ventas']; 
$cargo_usuario = strtolower(trim($_SESSION['cargo'] ?? $_SESSION['rol'] ?? ''));

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

require_once 'conexion.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    
    $archivo_tmp = $_FILES['excel_file']['tmp_name'];
    $nombre_archivo = $_FILES['excel_file']['name'];
    
    // Validar extensión
    $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
    if (!in_array($extension, ['xls', 'xlsx'])) {
        header("Location: disponibilidad.php?status=error_extension");
        exit();
    }

    try {
        // Cargar el documento Excel
        $documento = IOFactory::load($archivo_tmp);
        $hojaActual = $documento->getActiveSheet();
        $filas = $hojaActual->toArray();

        // INICIAR TRANSACCIÓN SQL (Seguridad ante fallos)
        $conexion->begin_transaction();

        // Reemplazar TRUNCATE por DELETE dentro de la transacción
        $conexion->query("DELETE FROM disponibilidad_inventario");

        // Preparar la sentencia de inserción
        $sql = "INSERT INTO disponibilidad_inventario (codigo, producto, categoria, cantidad, dias_venta, pen_liberar) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql);

        $categoria_actual = "GENERAL";

        foreach ($filas as $fila) {
            $codigo      = trim((string)($fila[0] ?? ''));
            $producto    = trim((string)($fila[1] ?? ''));
            $cant        = trim((string)($fila[2] ?? ''));
            $dias_venta  = trim((string)($fila[3] ?? ''));
            $pen_liberar = trim((string)($fila[4] ?? ''));

            // Omitir filas vacías o de encabezados/totales
            if (empty($codigo) && empty($producto)) continue;
            if (in_array(strtoupper($codigo), ['CODIGO', 'TOTALES']) || in_array(strtoupper($producto), ['PRODUCTOS', 'TOTALES'])) continue;

            // Detección de Categoría
            if (!empty($codigo) && empty($producto) && empty($cant)) {
                $categoria_actual = strtoupper($codigo);
                continue;
            }

            // Sanitización de valores numéricos (extrae solo números)
            $dias_redondeados = is_numeric($dias_venta) ? round((float)$dias_venta) : 0;
            $cant_limpia = intval(preg_replace('/[^\d]/', '', $cant));
            $pen_liberar_limpia = intval(preg_replace('/[^\d]/', '', $pen_liberar));

            // Insertar fila
            $stmt->bind_param("sssiii", $codigo, $producto, $categoria_actual, $cant_limpia, $dias_redondeados, $pen_liberar_limpia);
            $stmt->execute();
        }

        $stmt->close();
        
        // Confirmar los cambios si todo la lectura fue exitosa
        $conexion->commit();

        header("Location: disponibilidad.php?status=success");
        exit();

    } catch (Exception $e) {
        // En caso de cualquier error en la lectura o base de datos, revertir cambios
        if ($conexion->connect_errno === 0) {
            $conexion->rollback();
        }
        header("Location: disponibilidad.php?status=error_procesamiento");
        exit();
    }

} else {
    header("Location: disponibilidad.php");
    exit();
}
?>