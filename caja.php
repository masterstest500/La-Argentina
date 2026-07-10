<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['cargo'] !== 'ventas') {
    header('Location: index.php?error=acceso_denegado');
    exit();
}
?>