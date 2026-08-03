<?php
session_start();

// 1. Verificación de autenticación
$cargos_autorizados = ['preventista', 'administrador']; 
$cargo_usuario = strtolower($_SESSION['cargo'] ?? $_SESSION['rol'] ?? '');

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

// Incluir tu conexión a la base de datos (Asegúrate de que la ruta a tu conexion.php sea correcta)
require_once 'conexion.php'; // o la ruta donde tengas tu archivo de conexion
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
    
    $archivo_tmp = $_FILES['excel_file']['tmp_name'];
    $nombre_archivo = $_FILES['excel_file']['name'];
    
    $extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
    if (!in_array(strtolower($extension), ['xls', 'xlsx'])) {
        header("Location: neveras.php?status=error_extension");
        exit();
    }

    try {
        $documento = IOFactory::load($archivo_tmp);
        $hojaActual = $documento->getActiveSheet();
        $filas = $hojaActual->toArray();

        // Vaciamos la tabla para cargar los nuevos datos frescos del Excel
        $conexion->query("TRUNCATE TABLE disponibilidad_inventario");

        // Preparamos la sentencia SQL de inserción
        $stmt = $conexion->prepare("INSERT INTO disponibilidad_inventario (codigo, producto, categoria, cantidad, dias_venta, pen_liberar) VALUES (?, ?, ?, ?, ?, ?)");

        $categoria_actual = "GENERAL";

        foreach ($filas as $fila) {
            $codigo      = trim((string)($fila[0] ?? ''));
            $producto    = trim((string)($fila[1] ?? ''));
            $cant        = trim((string)($fila[2] ?? ''));
            $dias_venta  = trim((string)($fila[3] ?? ''));
            $pen_liberar = trim((string)($fila[4] ?? ''));

            if (empty($codigo) && empty($producto)) continue;
            if (strtoupper($codigo) === 'CODIGO' || strtoupper($producto) === 'PRODUCTOS') continue;
            if (strtoupper($codigo) === 'TOTALES' || strtoupper($producto) === 'TOTALES') continue;

            // Detección de Categoría
            if (!empty($codigo) && empty($producto) && empty($cant)) {
                $categoria_actual = strtoupper($codigo);
                continue;
            }

            // Sanitización y redondeo
            $dias_redondeados = is_numeric($dias_venta) ? round((float)$dias_venta) : 0;
            // Limpiar puntos de miles si existen en la cantidad (ej: 2.211 -> 2211)
            $cant_limpia = intval(str_replace('.', '', $cant));
            $pen_liberar_limpia = intval(str_replace('.', '', $pen_liberar));

            // Insertar en la base de datos
            $stmt->bind_param("sssiii", $codigo, $producto, $categoria_actual, $cant_limpia, $dias_redondeados, $pen_liberar_limpia);
            $stmt->execute();
        }

        $stmt->close();
        
        // Redirección con éxito
        header("Location: neveras.php?status=success");
        exit();

    } catch (Exception $e) {
        header("Location: neveras.php?status=error_procesamiento");
        exit();
    }

} else {
    header("Location: neveras.php");
    exit();
}
?>