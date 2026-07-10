<?php
session_start();
require_once 'conexion.php';

// --- PUERTA 1: SI ES PETICIÓN POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $nombre   = trim($_POST['nombre']);
    $cedula   = trim($_POST['cedula']);
    // ... resto de variables ...

    // (Tu lógica de registro existente va aquí)
    
    // Si todo sale bien:
    echo json_encode(["status" => "success"]);
    exit; // ¡MUY IMPORTANTE!
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crea una Cuenta</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/navbar.css">
</head>
<body class="login-body">

    <?php include 'navbar.php'; ?>

    <div class="registro-container">
        </div>

    <script src="js/registro.js"></script>
</body>
</html>