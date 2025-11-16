<?php
session_start();
require '../config/conexion.php'; // Asegúrate que la ruta a tu conexión es correcta

// --- Bloque de Seguridad ---
// CAMBIO: Ahora solo revisa si está logueado, no si es admin
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}
// CAMBIO: Obtenemos el ID del trabajador
$id_trabajador_actual = $_SESSION['user_id']; 
// --- Fin Bloque de Seguridad ---

// Verificar que los datos vienen por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recoger datos principales
    $id_cliente = $_POST['id_cliente'] ?? 0;
    
    // 2. Recoger datos de los productos (son arrays)
    $id_productos = $_POST['id_producto'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];
    
    // Validaciones básicas
    if (empty($id_cliente) || empty($id_productos)) {
        // CAMBIO: Redirige al formulario 'common'
        $_SESSION['error_message'] = "Error: Faltan datos del cliente o productos.";
        header('Location: ../common/venta_nueva.php');
        exit;
    }

    // Iniciar la transacción
    $pdo->beginTransaction();

    try {
        // --- PASO 1: Validar stock y calcular total (del lado del servidor) ---
        $total_calculado = 0;
        
        for ($i = 0; $i < count($id_productos); $i++) {
            $id_prod = $id_productos[$i];
            $cantidad = (int) $cantidades[$i];
            
            if (empty($id_prod) || $cantidad <= 0) {
                throw new Exception("Hay una fila de producto inválida o con cantidad 0.");
            }
            
            // Consultar el producto CON BLOQUEO (FOR UPDATE)
            $stmt_check = $pdo->prepare("SELECT precio, stock FROM producto WHERE id_producto = ? FOR UPDATE");
            $stmt_check->execute([$id_prod]);
            $producto = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                throw new Exception("El producto con ID $id_prod no existe.");
            }

            if ($producto['stock'] < $cantidad) {
                throw new Exception("Stock insuficiente para el producto ID $id_prod. Stock disponible: " . $producto['stock']);
            }
            
            // Usamos el precio de la BD, no el del formulario, por seguridad
            $total_calculado += $producto['precio'] * $cantidad;
        }

        // --- PASO 2: Insertar la venta principal ---
        // CAMBIO: Añadimos id_trabajador y el id_producto de la tabla 'venta' lo ponemos NULL
        $stmt_venta = $pdo->prepare("INSERT INTO venta (id_cliente, id_producto, fecha_venta, total, id_trabajador) 
                                     VALUES (?, NULL, CURDATE(), ?, ?)");
        $stmt_venta->execute([$id_cliente, $total_calculado, $id_trabajador_actual]);
        
        // Obtener el ID de la venta que acabamos de crear
        $id_venta = $pdo->lastInsertId();

        // --- PASO 3: Insertar detalles y actualizar stock ---
        for ($i = 0; $i < count($id_productos); $i++) {
            $id_prod = $id_productos[$i];
            $cantidad = (int) $cantidades[$i];
            
            $stmt_price = $pdo->prepare("SELECT precio FROM producto WHERE id_producto = ?");
            $stmt_price->execute([$id_prod]);
            $precio_unit = $stmt_price->fetchColumn();
            $subtotal = $precio_unit * $cantidad;

            // Insertar en detalle_venta
            $stmt_detalle = $pdo->prepare("INSERT INTO detalle_venta (id_venta, id_producto, cantidad, subtotal) VALUES (?, ?, ?, ?)");
            $stmt_detalle->execute([$id_venta, $id_prod, $cantidad, $subtotal]);

            // Actualizar el stock del producto
            $stmt_stock = $pdo->prepare("UPDATE producto SET stock = stock - ? WHERE id_producto = ?");
            $stmt_stock->execute([$cantidad, $id_prod]);
        }

        // --- PASO 4: Si todo salió bien, confirmar la transacción ---
        $pdo->commit();

        // CAMBIO: Redirigir a la lista 'common'
        $_SESSION['success_message'] = "¡Venta #" . $id_venta . " registrada con éxito!";
        header("Location: ../common/ventas.php");
        exit;

    } catch (Exception $e) {
        // --- ERROR: Si algo falló, revertir TODOS los cambios ---
        $pdo->rollBack();
        
        // CAMBIO: Redirigir al formulario 'common' con mensaje de error
        $_SESSION['error_message'] = "Error al guardar la venta: " . $e->getMessage();
        header("Location: ../common/venta_nueva.php");
        die();
    }

} else {
    // Si alguien intenta acceder a este archivo directamente
    header("Location: ../common/ventas.php");
    exit;
}
?>