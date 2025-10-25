<?php
session_start();
require_once '../config/conexion.php';

redirect{
    if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
        // Usuario ya ha iniciado sesión, redirigir a index.php
        header('Location: ../index.php');
        exit();
    } else {
        // Usuario no ha iniciado sesión, redirigir a login.php
        header('Location: ../login.php');
        exit();
    }
}

?>