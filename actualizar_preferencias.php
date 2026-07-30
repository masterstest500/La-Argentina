<?php
// 1. 🛡️ Escudo de Seguridad y Cabecera JSON
session_start();
header('Content-Type: application/json');

// Si no hay sesión, rechazar la petición inmediatamente
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado. Sesión inválida.']);
    exit();
}

include('conexion.php');
$usuario_id = $_SESSION['usuario_id'];

// 2. 🔍 Validación de Parámetros de Entrada
if (!isset($_POST['preferencia']) || !isset($_POST['estado'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos de solicitud incompletos.']);
    exit();
}

$preferencia = trim($_POST['preferencia']);
$estado = intval($_POST['estado']);

// 3. 📝 LISTA BLANCA (Seguridad estricta contra Inyección SQL en nombres de columnas)
$columnas_permitidas = ['pref_stock', 'pref_pdf', 'pref_datos'];

if (!in_array($preferencia, $columnas_permitidas)) {
    echo json_encode(['status' => 'error', 'message' => 'Intento de manipulación de campo no autorizado.']);
    exit();
}

// Asegurar que el estado sea estrictamente booleano (0 o 1)
if ($estado !== 0 && $estado !== 1) {
    echo json_encode(['status' => 'error', 'message' => 'Valor de estado inválido.']);
    exit();
}

// 4. 💾 Actualización Segura en la Base de Datos usando Consultas Preparadas
$sql_update = "UPDATE usuarios SET $preferencia = ? WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql_update);

if ($stmt) {
    // Vinculamos 'i' (entero para el estado) e 'i' (entero para el ID de usuario)
    mysqli_stmt_bind_param($stmt, "ii", $estado, $usuario_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // Respuesta exitosa que leerá JavaScript
        echo json_encode(['status' => 'success', 'message' => 'Preferencia guardada de forma asíncrona.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al ejecutar la actualización en BD.']);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error en la preparación de la consulta SQL.']);
}

mysqli_close($conexion);
?>