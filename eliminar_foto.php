<?php
// 1. Iniciar sesión y conectar a la base de datos
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Validar que la petición venga estrictamente por POST y con la acción correcta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_perfil']) && $_POST['accion_perfil'] === 'eliminar_foto') {
    
    include('conexion.php');
    $usuario_id = $_SESSION['usuario_id'];

    // 2. Buscar la ruta de la foto actual en la base de datos
    $sql = "SELECT foto_perfil FROM usuarios WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $usuario_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $foto_actual);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // 3. Borrar el archivo físico del disco duro del servidor
        if (!empty($foto_actual) && file_exists($foto_actual)) {
            unlink($foto_actual); // 🔥 Elimina físicamente el archivo de la carpeta
        }

        // 4. Actualizar el campo en la base de datos a NULL
        $sql_delete = "UPDATE usuarios SET foto_perfil = NULL WHERE id = ?";
        $stmt_delete = mysqli_prepare($conexion, $sql_delete);
        
        if ($stmt_delete) {
            mysqli_stmt_bind_param($stmt_delete, "i", $usuario_id);
            mysqli_stmt_execute($stmt_delete);
            mysqli_stmt_close($stmt_delete);
        }
    }

    mysqli_close($conexion);
    
    // Redireccionar al perfil indicando que se eliminó con éxito
    header("Location: perfil.php?status=foto_eliminada");
    exit();
} else {
    // Si intentan entrar a este archivo directamente por URL, los mandamos al perfil
    header("Location: perfil.php");
    exit();
}
?>