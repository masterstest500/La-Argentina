<?php
session_start();
require_once 'conexion.php';

// --- LÓGICA BACKEND (Segura contra Inyección SQL) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Saneamiento de datos para seguridad
    $cedula   = mysqli_real_escape_string($conexion, trim($_POST['cedula']));
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
    exit; // Detiene la ejecución aquí para no imprimir el HTML en la respuesta JSON
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:image" content="img/logos/logo3.png">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-body">

    <div class="login-container">
        <div class="login-logo">
            <img src="img/logos/logo3.png" alt="Helados La Argentina"> 
            <h2>¡Hola Trabajador!</h2>
            <p>Antes de entrar, por favor inicie sesión.</p>
        </div>

        <form id="login-form">
            <div class="input-group">
                <label for="cedula">Cédula de Identidad</label>
                <input type="text" id="cedula" placeholder="Ej. 12345678" maxlength="8" required autocomplete="username">
            </div>

            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" placeholder="••••••••" maxlength="10" required autocomplete="current-password">
            </div>

            <div id="error-message" class="error-box hidden">
                <span class="error-icon">⚠️</span>
                <span id="error-text">Usuario o contraseña incorrectos.</span>
            </div>

            <div class="login-opciones">
                <a href="registro.php" class="login-link">Registrarse</a>
                <a href="olvidar-contraseña.php" class="login-link">¿Olvidó su contraseña?</a>
            </div>
            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>
    </div>

    <script src="js/login.js"></script>
</body>
</html>