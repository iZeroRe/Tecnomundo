<?php 
session_start();
require '../config/conexion.php';

// Verificar inicio de sesion tanto para admin como trabajador
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}

//Verificaccion de enciado ID por GET
if(!isset($_GET['reparacion_id']) || !is_numeric($_GET['reparacion_id'])) {
    $_SESSION['error_message'] = "Error: ID de reparación no válido.";
    header('Location: ../common/ordenes.php');
    exit;
}

$id_reparacion = $_GET['reparacion_id'];
    $pdo->beginTransaction();

try{
$sql_data = "SELECT 
                    r.costo, 
                    e.id_cliente, 
                    c.id_empresa 
                 FROM reparacion AS r
                 JOIN equipo AS e ON r.id_equipo = e.id_equipo
                 JOIN cliente AS c ON e.id_cliente = c.id_cliente
                 WHERE r.id_reparacion = ?";
    
    $stmt_data = $pdo->prepare($sql_data);
    $stmt_data->execute([$id_reparacion]);
    $datos = $stmt_data->fetch(PDO::FETCH_ASSOC);

    if (!$datos) {
        // Si no se encuentra la reparación, lanzamos un error
        throw new Exception("La reparación con ID $id_reparacion no existe.");
    }
    $costo_a_pagar = $datos['costo'];
    $id_cliente = $datos['id_cliente'];
    $id_empresa = $datos['id_empresa'];
    //Verificacion si ya pago 
    $stmt_check = $pdo->prepare("SELECT id_factura FROM factura WHERE id_reparacion = ?");
    $stmt_check->execute([$id_reparacion]);
    if ($stmt_check->fetch()) {
        // Si ya existe una factura, lanzamos un error
        throw new Exception("Esta orden ya ha sido cobrada anteriormente.");
    }
// Creacion de factura
    $sql_factura = "INSERT INTO factura (id_reparacion, id_venta, fecha_emision, id_cliente, id_empresa, detalle) 
                    VALUES (?, NULL, CURDATE(), ?, ?, ?)";
    $detalle_factura = "Servicio de reparación folio #" . $id_reparacion;
    
    $pdo->prepare($sql_factura)->execute([
        $id_reparacion,
        $id_cliente,
        $id_empresa,
        $detalle_factura
    ]);
    // ID de la factura
    $id_nueva_factura = $pdo->lastInsertId();

    
    // Registramos el pago
    $sql_pago = "INSERT INTO pago (id_factura, fecha_pago, monto_pago, metodo_pago, detalle) 
                 VALUES (?, CURDATE(), ?, ?, ?)";
    $metodo_pago = "Efectivo"; 
    $detalle_pago = "Pago de factura #" . $id_nueva_factura;
    
    $pdo->prepare($sql_pago)->execute([
        $id_nueva_factura,
        $costo_a_pagar,
        $metodo_pago,
        $detalle_pago
    ]);

    
    // Hacemos la garnatia en automatico 
    $sql_garantia = "INSERT INTO garantia (id_reparacion, id_venta, fecha_inicio, fecha_fin, condiciones) 
                     VALUES (?, NULL, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?)";
    $condiciones_garantia = "Garantía de 30 días sobre la reparación #" . $id_reparacion;
    
    $pdo->prepare($sql_garantia)->execute([
        $id_reparacion,
        $condiciones_garantia
    ]);

    
    $pdo->commit();
    
    $_SESSION['success_message'] = "¡Orden #" . $id_reparacion . " cobrada! Pago y garantía registrados con éxito.";
    header('Location: ../common/ordenes.php'); // Redirigimos de vuelta a la lista de órdenes
    exit;

} catch (Exception $e) {
    // En caso de un error desacemos todo 
    $pdo->rollBack();
    
    $_SESSION['error_message'] = "Error al registrar el pago: " . $e->getMessage();
    
    header('Location: ../common/ordenes.php'); // Redirigimos de vuelta a la lista de órdenes
    exit;
}

catch (Exception $e) {
    // En caso de un error desacemos todo 
    $pdo->rollBack();

    $_SESSION['error_message'] = "Error al registrar el pago: " . $e->getMessage();

    header('Location: ../common/ordenes.php');
    exit;
}
?>