<?php
session_start();
require_once 'conexion.php';

// --- LÓGICA BACKEND (Segura contra Inyección SQL) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Saneamiento de datos para evitar ataques
    $nombre   = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $cedula   = mysqli_real_escape_string($conexion, trim($_POST['cedula']));
    $telefono = mysqli_real_escape_string($conexion, trim($_POST['telefono']));
    $correo   = mysqli_real_escape_string($conexion, trim($_POST['correo']));
    $password = mysqli_real_escape_string($conexion, $_POST['password']);
    $cargo    = mysqli_real_escape_string($conexion, trim($_POST['cargo']));

    if (empty($nombre) || empty($cedula) || empty($correo) || empty($password) || empty($cargo)) {
        echo json_encode(["status" => "error", "message" => "Por favor, rellene todos los campos obligatorios."]);
        exit;
    }

    $buscar_usuario = "SELECT * FROM usuarios WHERE cedula = '$cedula' OR correo = '$correo'";
    $verificacion = mysqli_query($conexion, $buscar_usuario);

    if (mysqli_num_rows($verificacion) > 0) {
        echo json_encode(["status" => "error", "message" => "Error: Esta Cédula o Correo ya se encuentra registrada."]);
        exit;
    }

    $sql_insertar = "INSERT INTO usuarios (cedula, nombre, telefono, correo, password, cargo) 
                     VALUES ('$cedula', '$nombre', '$telefono', '$correo', '$password', '$cargo')";

    if (mysqli_query($conexion, $sql_insertar)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error interno del servidor."]);
    }
    exit; // Detiene la ejecución
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:image" content="img/logos/logo3.png">
    <title>Crea una Cuenta</title>
    <link rel="stylesheet" href="css/login.css"> 
</head>
<body class="login-body">

    <div class="registro-container">
        <div class="login-logo">
            <img src="img/logos/logo3.png" alt="Helados La Argentina"> 
            <h2>Registro de Personal</h2>
            <p>Cree una cuenta para acceder a sus herramientas de trabajo.</p>
        </div>

        <form id="registro-form" autocomplete="off">
            <div class="form-grid">
                
                <div class="input-group">
                    <label for="reg-nombre">Nombre y Apellido</label>
                    <input type="text" id="reg-nombre" placeholder="Ej. Eliezer Chirinos" autocomplete="off" required>
                </div>

                <div class="input-group">
                    <label for="reg-cedula">Cédula de Identidad</label>
                    <input type="text" id="reg-cedula" placeholder="Ej. 31110444" maxlength="8" autocomplete="off" required>
                </div>

                <div class="input-group">
                    <label for="reg-telefono">Número de Teléfono</label>
                    <input type="tel" id="reg-telefono" placeholder="Ej. 04121234567" maxlength="11" autocomplete="off" required>
                </div>

                <div class="input-group">
                    <label for="reg-correo">Correo Electrónico</label>
                    <input type="email" id="reg-correo" placeholder="ejemplo@correo.com" autocomplete="off" required>
                </div>

                <div class="input-group">
                    <label for="reg-password">Contraseña</label>
                    <input type="password" id="reg-password" placeholder="••••••••" maxlength="10" autocomplete="new-password" required>
                </div>

                <div class="input-group">
                    <label for="reg-cargo">Cargo en la Empresa</label>
                    <select id="reg-cargo" required class="reg-select">
                        <option value="" disabled selected>Seleccione su cargo...</option>
                        <option value="preventista">Preventista</option>
                        <option value="administrador">Administrador</option>
                        <option value="ventas">Ventas</option>
                    </select>
                </div>

                <div id="registro-error" class="error-box hidden full-width">
                    <span class="error-icon">⚠️</span>
                    <span id="registro-error-text">Por favor, rellene todos los campos.</span>
                </div>

                <div class="login-opciones full-width" style="justify-content: center;">
                    <a href="login.php" class="login-link">¿Ya tiene una cuenta? Inicie Sesión</a>
                </div>

                <button type="submit" class="btn-login full-width">Registrarse</button>
            </div>
        </form>
    </div>

    <script src="js/registro.js"></script>
</body>
</html>