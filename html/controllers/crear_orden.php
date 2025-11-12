<?php 
session_start();
require '../config/conexion.php';

//1 Verificar inicio de sesion
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true || $_SESSION['user_rol'] !== 'admin') {
    // Si no es admin, lo sacamos.
    header('Location: /login.php');
    exit;
}
//2 Verificar que los datos se hayan enviado por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si alguien intenta acceder a este archivo escribiendo la URL, lo sacamos.
    die("Acceso no autorizado.");
}
// Datos
if (!isset($_SESSION['user_id'])) {
    die("Error crítico: No se encontró el ID del trabajador en la sesión.");
}

// Recibimos los daots del formulario
$id_cliente = $_POST['id_cliente'];
$marca = $_POST['marca'];
$modelo = $_POST['modelo'];
$falla = $_POST['falla'];
// Para la pieza, verificamos si seleccionaron una o la dejaron en "opcional"
$id_producto = !empty($_POST['id_producto']) ? $_POST['id_producto'] : null;
$costo = $_POST['costo'];
$fecha_promesa = $_POST['fecha_promesa'];
// ID de que esta creando la orden
$id_trabajador = $_SESSION['user_id'];

// Enviar a la base de datos
$pdo->beginTransaction();

try{
//Insert para el equipo
    $sql_equipo = "INSERT INTO equipo (id_cliente, marca, modelo, observaciones, fecha) VALUES (?, ?, ?, ?, CURDATE())";
    $stmt_equipo = $pdo->prepare($sql_equipo);
    $stmt_equipo->execute([$id_cliente, $marca, $modelo, $falla]);
    // ID del equipo recien creado
    $id_nuevo_equipo = $pdo->lastInsertId(); //Problamente de error

    $sql_reparacion = "INSERT INTO reparacion (id_equipo, id_trabajador, fecha_ingreso, fecha_terminado, costo) 
                       VALUES (?, ?, CURDATE(), ?, ?)";
    $stmt_reparacion = $pdo->prepare($sql_reparacion);
    $stmt_reparacion->execute([$id_nuevo_equipo, $id_trabajador, $fecha_promesa, $costo]);
    // ID reparacion
    $id_nueva_reparacion = $pdo->lastInsertId();
    // Insertar el dellate posible NULL
    if($id_producto !== null){
        // Verificar precios
        $stmt_precio = $pdo->prepare("SELECT precio FROM producto WHERE id_producto = ?");
        $stmt_precio->execute([$id_producto]);
        $precio_pieza = $stmt_precio->fetchColumn();

        $subtotal_pieza = ($precio_pieza) ? $precio_pieza : 0;
        $sql_detalle = "INSERT INTO detalle_reparacion (id_reparacion, id_producto, cantidad, subtotal) 
                        VALUES (?, ?, 1, ?)";
        $stmt_detalle = $pdo->prepare($sql_detalle);
        $stmt_detalle->execute([$id_nueva_reparacion, $id_producto, $subtotal_pieza]);
    }

    //Confirmacion de transaccion a la BD
    $pdo->commit();
    $_SESSION['success_message'] = "¡Orden #" . $id_nueva_reparacion . "creada con éxito!";

    // Regreso al dashboard
    header('Location: ../admin/dashboard.php');
    exit;
}
catch (PDOException $e) {
    // Por si algo falla reacemos los cambios
    $pdo->rollBack();

    //Mensaje de error
    $_SESSION['error_message'] = "Error para guardar la orden:" . $e->getMessage();

    // Redirigimos de vuelta al formulario de nueva orden
    header('Location: ../admin/nueva_orden.php');
    exit;
}
?>