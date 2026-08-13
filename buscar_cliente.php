<?php
// buscar_cliente.php

// 1. Indicamos que la respuesta será en formato JSON
header('Content-Type: application/json; charset=utf-8');

// 2. Incluimos la conexión a la base de datos
include('conexion.php');

// 3. Verificamos si se recibió el RIF por la URL (método GET)
if (isset($_GET['rif'])) {
    
    // Limpiamos el dato para evitar inyecciones SQL
    $rif_buscado = mysqli_real_escape_string($conexion, trim($_GET['rif']));
    
    // 4. Armamos la consulta buscando exactamente el RIF
    $query = "SELECT * FROM captaciones WHERE rif_cliente = '$rif_buscado' LIMIT 1";
    $resultado = mysqli_query($conexion, $query);
    
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        // Cliente encontrado: extraemos todos sus datos como un arreglo asociativo
        $cliente = mysqli_fetch_assoc($resultado);
        
        // Devolvemos la respuesta exitosa en JSON
        echo json_encode([
            'success' => true,
            'data' => $cliente
        ]);
    } else {
        // RIF no encontrado en la base de datos
        echo json_encode([
            'success' => false,
            'mensaje' => 'No se encontró ninguna captación con el RIF proporcionado.'
        ]);
    }
} else {
    // Falla de solicitud: no se envió el RIF
    echo json_encode([
        'success' => false,
        'mensaje' => 'Falta el parámetro RIF en la petición.'
    ]);
}
?>