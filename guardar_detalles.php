<?php
session_start();

// 1. Verificación de autenticación y seguridad
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['user']) && !isset($_SESSION['nombre']) && !isset($_SESSION['cargo'])) {
    header("Location: login.php");
    exit();
}

$cargos_autorizados = ['preventista', 'administrador']; 
$cargo_usuario = strtolower($_SESSION['cargo'] ?? $_SESSION['rol'] ?? '');

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    header("Location: index.php?error=acceso_denegado");
    exit();
}

// 2. Conexión a la base de datos
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_producto = $_POST['codigo_producto'] ?? '';
    $presentacion    = trim($_POST['presentacion'] ?? '');
    $ingredientes    = trim($_POST['ingredientes'] ?? '');

    if (empty($codigo_producto)) {
        header("Location: catalogo.php?error=codigo_vacio");
        exit();
    }

    $ruta_imagen_sql = "";
    $actualizar_imagen = false;

    // 3. Procesamiento, validación de formato y control de peso de la Imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['imagen']['tmp_name'];
        $fileName      = $_FILES['imagen']['name'];
        $fileSize      = $_FILES['imagen']['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Extensiones permitidas por seguridad
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        // Límite máximo de tamaño configurado en 2 Megabytes (2 * 1024 * 1024 bytes)
        $maxFileSize = 2 * 1024 * 1024; 
        
        if (in_array($fileExtension, $allowedExtensions) && $fileSize <= $maxFileSize) {
            // Carpeta destino especificada
            $uploadFileDir = 'img/helados/';
            
            // Crear la carpeta automáticamente si no existe en el servidor
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }

            // Generar un nombre único cifrado para evitar conflictos de nombres
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $ruta_imagen_sql = $dest_path;
                $actualizar_imagen = true;
            }
        } else {
            // Si el archivo supera los 2 MB o tiene una extensión no permitida
            header("Location: catalogo.php?error=archivo_no_valido");
            exit();
        }
    }

    // 4. Verificamos si ya existe un registro previo para este producto en la base de datos
    $stmt_check = $conexion->prepare("SELECT ruta_imagen FROM detalles_catalogo WHERE codigo_producto = ?");
    $stmt_check->bind_param("s", $codigo_producto);
    $stmt_check->execute();
    $resultado_check = $stmt_check->get_result();

    if ($resultado_check->num_rows > 0) {
        // El producto ya tiene detalles guardados -> Hacemos UPDATE
        $row_actual = $resultado_check->fetch_assoc();
        
        // Si el usuario no subió una nueva imagen, conservamos la que ya estaba guardada
        if (!$actualizar_imagen) {
            $ruta_imagen_sql = $row_actual['ruta_imagen'];
        }

        $stmt_update = $conexion->prepare("UPDATE detalles_catalogo SET presentacion = ?, ingredientes = ?, ruta_imagen = ? WHERE codigo_producto = ?");
        $stmt_update->bind_param("ssss", $presentacion, $ingredientes, $ruta_imagen_sql, $codigo_producto);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        // Es la primera vez que se edita este producto -> Hacemos INSERT
        $stmt_insert = $conexion->prepare("INSERT INTO detalles_catalogo (codigo_producto, presentacion, ingredientes, ruta_imagen) VALUES (?, ?, ?, ?)");
        $stmt_insert->bind_param("ssss", $codigo_producto, $presentacion, $ingredientes, $ruta_imagen_sql);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
    
    $stmt_check->close();

    // 5. Redirigir de vuelta al catálogo para ver los cambios reflejados
    header("Location: catalogo.php?status=exito");
    exit();

} else {
    header("Location: catalogo.php");
    exit();
}
?>