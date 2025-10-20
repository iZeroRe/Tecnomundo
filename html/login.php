<?php
// En /html/login.php

// Inicia la sesión.
session_start();

// Si el usuario ya está logueado, lo redirigimos a index.php
if(isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    header('Location: /index.php');
    exit();
}

// Incluimos el controlador para que maneje el formulario.
include 'controllers/ControladorAuth.php';