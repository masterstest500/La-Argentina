<?php
session_start();

// 1. Verificación de autenticación básica
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['cargo'])) {
    header("Location: login.php");
    exit();
}

// 2. Control de accesos por roles (Filtro estricto)
// REGLA: Define aquí adentro qué cargos SI pueden ver esta página específica
$cargos_autorizados = ['ventas', 'administrador']; 

$cargo_usuario = strtolower($_SESSION['cargo'] ?? '');

if (!in_array($cargo_usuario, $cargos_autorizados)) {
    // Si no está autorizado, lo expulsamos al index con el parámetro de error
    // Tu archivo main.js detectará esto automáticamente y disparará el Toast estético
    header("Location: index.php?error=acceso_denegado");
    exit();
}
?>