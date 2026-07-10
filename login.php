<?php
session_start();
require_once 'conexion.php';

// --- PUERTA 1: SI ES PETICIÓN POST (AJAX DESDE JS) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json'); // Solo para la respuesta JSON
    
    $cedula   = trim($_POST['cedula']);
    $password = $_POST['password'];

    if (empty($cedula) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Por favor, llene todos los campos."]);
        exit;
    }

    $sql_buscar = "SELECT nombre, cargo, password FROM usuarios WHERE cedula = '$cedula'";
    $resultado = mysqli_query($conexion, $sql_buscar);

    if (mysqli_num_rows($resultado) > 0) {
        $usuario = mysqli_fetch_assoc($resultado);
        if ($password === $usuario['password']) {
            $_SESSION['user']  = trim($usuario['nombre']);
            $_SESSION['cargo'] = trim($usuario['cargo']);
            echo json_encode(["status" => "success", "nombre" => $usuario['nombre'], "cargo" => $usuario['cargo']]);
        } else {
            echo json_encode(["status" => "error", "message" => "La contraseña introducida es incorrecta."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Cédula no registrada."]);
    }
    exit; // ¡MUY IMPORTANTE! Detiene el archivo aquí para no enviar el HTML
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/navbar.css">
</head>
<body class="login-body">

    <?php include 'navbar.php'; ?>

    <div class="login-container">
        <form id="login-form">
            <div class="form-group">
                <label for="cedula">Cédula:</label>
                <input type="text" id="cedula" name="cedula" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Iniciar Sesión</button>
        </form>
    </div>

    <script src="js/login.js"></script>
</body>
</html>