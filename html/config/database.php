<?php

$host     = "db";
$usuario  = "root";             
$password = "root";               
$base_datos = "tecnomundo"; 


$conexion = new mysqli($host, $usuario, $password, $base_datos);


if ($conexion->connect_error) {
    die("Error de Conexión (" . $conexion->connect_errno . ") " . $conexion->connect_error);
}

$conexion->set_charset("utf8");

?>