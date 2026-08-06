<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $id = $_POST['id'] ?? null;
    $codigo = trim($_POST['codigo_producto'] ?? '');
    $nombre = trim($_POST['nombre_producto'] ?? '');
    $presentacion = trim($_POST['presentacion'] ?? '');
    $ingredientes = trim($_POST['ingredientes'] ?? '');

    // Gestión de subida de archivo de imagen
    $ruta_imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombre_archivo = time() . '_' . basename($_FILES['imagen']['name']);
        $directorio_destino = 'img/helados/';
        
        if (!is_dir($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }
        
        $ruta_completa = $directorio_destino . $nombre_archivo;
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_completa)) {
            $ruta_imagen = $ruta_completa;
        }
    }

    if ($accion === 'crear') {
        // 1. Insertar en detalles_catalogo
        $stmt = $conexion->prepare("INSERT INTO detalles_catalogo (codigo_producto, nombre_producto, ingredientes, presentacion, ruta_imagen) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $codigo, $nombre, $ingredientes, $presentacion, $ruta_imagen);
        $stmt->execute();

        // 2. Insertar o actualizar en disponibilidad_inventario para que se vea en catalogo.php
        $stmt_inv = $conexion->prepare("INSERT INTO disponibilidad_inventario (codigo, producto, categoria, cantidad) VALUES (?, ?, 'GENERAL', 1) ON DUPLICATE KEY UPDATE producto = VALUES(producto)");
        $stmt_inv->bind_param("ss", $codigo, $nombre);
        $stmt_inv->execute();

    } elseif ($accion === 'editar') {
        if ($ruta_imagen) {
            // Si subió una foto nueva, la actualizamos
            $stmt = $conexion->prepare("UPDATE detalles_catalogo SET nombre_producto = ?, presentacion = ?, ingredientes = ?, ruta_imagen = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $nombre, $presentacion, $ingredientes, $ruta_imagen, $id);
        } else {
            // Conservamos la imagen existente
            $stmt = $conexion->prepare("UPDATE detalles_catalogo SET nombre_producto = ?, presentacion = ?, ingredientes = ? WHERE id = ?");
            $stmt->bind_param("sssi", $nombre, $presentacion, $ingredientes, $id);
        }
        $stmt->execute();

        // Sincronizar el nombre con la tabla de inventario
        $stmt_inv = $conexion->prepare("UPDATE disponibilidad_inventario SET producto = ? WHERE codigo = ?");
        $stmt_inv->bind_param("ss", $nombre, $codigo);
        $stmt_inv->execute();
    }

    header("Location: editar-catalogo.php?msj=exito");
    exit();

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['accion']) && $_GET['accion'] === 'eliminar') {
    $id = intval($_GET['id']);
    
    // Obtener código de producto antes de borrar
    $stmt_cod = $conexion->prepare("SELECT codigo_producto FROM detalles_catalogo WHERE id = ?");
    $stmt_cod->bind_param("i", $id);
    $stmt_cod->execute();
    $res = $stmt_cod->get_result()->fetch_assoc();
    
    if ($res) {
        $codigo = $res['codigo_producto'];
        
        // Borrar de detalles_catalogo
        $stmt = $conexion->prepare("DELETE FROM detalles_catalogo WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Borrar de disponibilidad_inventario
        $stmt_inv = $conexion->prepare("DELETE FROM disponibilidad_inventario WHERE codigo = ?");
        $stmt_inv->bind_param("s", $codigo);
        $stmt_inv->execute();
    }

    header("Location: editar-catalogo.php?msj=eliminado");
    exit();
}