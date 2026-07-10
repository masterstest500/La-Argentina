<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['cargo'] !== 'preventista') {
    header('Location: index.php?error=acceso_denegado');
    exit();
}
?>