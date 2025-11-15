<?php
session_start();
require '../config/conexion.php'; 


// Verificar inicio de sesion tanto para admin como trabajador
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}

// Verificamos que los datos se hayan enviado por GET (desde el link <a>)
/*if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    die("Acceso no autorizado.");
}*/

// Verificamos que se haya enviado un ID por GET
if (!isset($_GET['venta_id']) || !is_numeric($_GET['venta_id'])) {
    $_SESSION['error_message'] = "Error: ID de venta no válido.";
    header('Location: ../admin/ventas.php'); // Redirige a ventas
    exit;
}
$id_venta = $_GET['venta_id'];

$pdo->beginTransaction();

try {
    
    // SQL para obtner los datos que estan en la venta
    $sql_data = "SELECT 
                    v.total, 
                    v.id_cliente, 
                    c.id_empresa 
                 FROM venta AS v
                 JOIN cliente AS c ON v.id_cliente = c.id_cliente
                 WHERE v.id_venta = ?";
    
    $stmt_data = $pdo->prepare($sql_data);
    $stmt_data->execute([$id_venta]);
    $datos = $stmt_data->fetch(PDO::FETCH_ASSOC);

    if (!$datos) {
        throw new Exception("La venta con ID $id_venta no existe.");
    }

    $costo_a_pagar = $datos['total']; // Este es el total en venta
    $id_cliente = $datos['id_cliente'];
    $id_empresa = $datos['id_empresa'];

    // Verificamos si ya se hizo el pago 
    $stmt_check = $pdo->prepare("SELECT id_factura FROM factura WHERE id_venta = ?");
    $stmt_check->execute([$id_venta]);
    if ($stmt_check->fetch()) {
        throw new Exception("Esta venta ya ha sido cobrada anteriormente.");
    }

    // Creacion de faactura
    $sql_factura = "INSERT INTO factura (id_reparacion, id_venta, fecha_emision, id_cliente, id_empresa, detalle) 
                    VALUES (NULL, ?, CURDATE(), ?, ?, ?)";
    $detalle_factura = "Venta de productos folio #" . $id_venta;
    
    $pdo->prepare($sql_factura)->execute([
        $id_venta,
        $id_cliente,
        $id_empresa,
        $detalle_factura
    ]);
    $id_nueva_factura = $pdo->lastInsertId();

    
    // Registro del pago
    $sql_pago = "INSERT INTO pago (id_factura, fecha_pago, monto_pago, metodo_pago, detalle) 
                 VALUES (?, CURDATE(), ?, ?, ?)";
    $metodo_pago = "Efectivo"; // Asumimos Efectivo
    $detalle_pago = "Pago de factura #" . $id_nueva_factura;
    
    $pdo->prepare($sql_pago)->execute([
        $id_nueva_factura,
        $costo_a_pagar,
        $metodo_pago,
        $detalle_pago
    ]);

    
    // Genera garantia autmoaticamnete
    $sql_garantia = "INSERT INTO garantia (id_reparacion, id_venta, fecha_inicio, fecha_fin, condiciones) 
                     VALUES (NULL, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), ?)";
    $condiciones_garantia = "Garantía de 30 días sobre la venta #" . $id_venta;
    
    $pdo->prepare($sql_garantia)->execute([
        $id_venta,
        $condiciones_garantia
    ]);

    $pdo->commit();
    
    $_SESSION['success_message'] = "¡Venta #" . $id_venta . " cobrada! Pago y garantía registrados con éxito.";
    header('Location: ../admin/ventas.php'); // Redirige a ventas
    exit;

} catch (Exception $e) {
    // EN caso de error se desane los cambios 
    $pdo->rollBack();
    
    $_SESSION['error_message'] = "Error al registrar el pago: " . $e->getMessage();
    header('Location: ../admin/ventas.php'); // Redirige a ventas
    exit;
}
?>