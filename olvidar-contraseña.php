<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="css/login.css"> 
</head>
<body class="login-body">

    <div class="login-container">
        <div class="login-logo">
            <img src="img/logos/logo3.png" alt="Helados La Argentina"> 
            <h2>¿Olvidó su contraseña?</h2>
            <p>Ingrese sus datos para establecer una nueva clave de acceso.</p>
        </div>

        <form id="recuperar-form">
            <div class="input-group">
                <label for="rec-cedula">Cédula de Identidad</label>
                <input type="text" id="rec-cedula" placeholder="Ej. 31110444" maxlength="8" required>
            </div>

            <div class="input-group">
                <label for="rec-correo">Correo Electrónico Registrado</label>
                <input type="email" id="rec-correo" placeholder="ejemplo@correo.com" required>
            </div>

            <div class="input-group">
                <label for="rec-password">Nueva Contraseña</label>
                <input type="password" id="rec-password" placeholder="Máx. 10 caracteres" maxlength="10" required>
            </div>

            <div class="input-group">
                <label for="rec-password-confirm">Confirmar Nueva Contraseña</label>
                <input type="password" id="rec-password-confirm" placeholder="Repita la contraseña" maxlength="10" required>
            </div>

            <div id="recuperar-error" class="error-box hidden">
                <span class="error-icon">⚠️</span>
                <span id="recuperar-error-text">Error general.</span>
            </div>

            <button type="submit" class="btn-login" style="margin-top: 1.5rem;">Actualizar Contraseña</button>
            
            <div class="login-opciones" style="justify-content: center; margin-top: 1rem;">
                <a href="login.php" class="login-link">Volver al Inicio de Sesión</a>
            </div>
        </form>
    </div>

    <script src="js/olvidar-contraseña.js"></script>
</body>
</html>