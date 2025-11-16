<?php 
session_start();
require '../config/conexion.php';

//Verificar inicio de sesion
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}
//Verificar que los datos se hayan enviado por POST
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
$fecha_promesa = $_POST['fecha_promesa'];
// ID de que esta creando la orden
$id_trabajador = $_SESSION['user_id'];
// Recibimos el costo extra del formulario
$costo_extra = (float)($_POST['costo_extra'] ?? 0);
$precio_pieza = 0;
// Enviar a la base de datos
$pdo->beginTransaction();

try {
    
    // --- PASO A: Obtener el precio de la pieza (SI SE SELECCIONÓ UNA) ---
    if ($id_producto !== null) {
        $stmt_precio = $pdo->prepare("SELECT precio FROM producto WHERE id_producto = ?");
        $stmt_precio->execute([$id_producto]);
        $precio_pieza_db = $stmt_precio->fetchColumn();
        
        if ($precio_pieza_db) {
            $precio_pieza = (float)$precio_pieza_db;
        }
    }
    
    // --- PASO B: Calcular el Costo Total en el servidor ---
    $costo_total_seguro = $precio_pieza + $costo_extra;


    // --- PASO C: Insertar el EQUIPO ---
    $sql_equipo = "INSERT INTO equipo (id_cliente, marca, modelo, observaciones, fecha) 
                   VALUES (?, ?, ?, ?, CURDATE())";
    
    $stmt_equipo = $pdo->prepare($sql_equipo);
    $stmt_equipo->execute([$id_cliente, $marca, $modelo, $falla]);
    $id_nuevo_equipo = $pdo->lastInsertId();

    // --- PASO D: Insertar la REPARACIÓN (con el costo seguro) ---
    $sql_reparacion = "INSERT INTO reparacion (id_equipo, id_trabajador, fecha_ingreso, fecha_terminado, costo) 
                       VALUES (?, ?, CURDATE(), ?, ?)";
    
    $stmt_reparacion = $pdo->prepare($sql_reparacion);
    // Usamos $costo_total_seguro en lugar de lo que venía de $_POST
    $stmt_reparacion->execute([$id_nuevo_equipo, $id_trabajador, $fecha_promesa, $costo_total_seguro]);
    $id_nueva_reparacion = $pdo->lastInsertId();

    // --- PASO E: Insertar el DETALLE (Opcional) ---
    if ($id_producto !== null) {
        $sql_detalle = "INSERT INTO detalle_reparacion (id_reparacion, id_producto, cantidad, subtotal) 
                        VALUES (?, ?, 1, ?)";
        $stmt_detalle = $pdo->prepare($sql_detalle);
        // Usamos $precio_pieza que ya obtuvimos
        $stmt_detalle->execute([$id_nueva_reparacion, $id_producto, $precio_pieza]);
    }

    // --- 4. Confirmar la Transacción ---
    $pdo->commit();
    
    $_SESSION['success_message'] = "¡Orden #" . $id_nueva_reparacion . " creada con éxito!";
    
    // Redirigir de vuelta a la lista de órdenes
    header('Location: ../common/ordenes.php');
    exit;

} catch (PDOException $e) {
    // --- 5. Manejo de Errores ---
    $pdo->rollBack();
    $_SESSION['error_message'] = "Error al guardar la orden: " . $e->getMessage();
    header('Location: ../common/nueva_orden.php');
    exit;
}
?>