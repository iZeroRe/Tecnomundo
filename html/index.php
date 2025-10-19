<?php
//Inciair la sesion
session_start();
// Verifica si no el usuario no ha iniciado sesion
if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true){
    header('Location: login.php'); //Si no ha iniciado sesion redirige a login
        exit();
}

// Si esta iniciada la sesion 
$rol_actual = $_SESSION['user_rol'];
$nombre_usuario = $_SESSION['user_name'];
$es_admin = ($rol_actual === 'admin'); //En caso de no ser el admin simplemente sabra que es trabajador

// En esta parte incluiremos lo que este en views


?>