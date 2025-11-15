<?php
session_start();
require '../config/conexion.php'; // Asegúrate que la ruta a tu conexión es correcta

// --- Bloque de Seguridad ---
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: /login.php');
    exit;
}
if ($_SESSION['user_rol'] !== 'admin') {
    header('Location: ../trabajador/dashboard.php');
    exit;
}
// --- Fin Bloque de Seguridad ---

// Verificar que los datos vienen por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Recoger datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $marca = $_POST['marca'] ?? '';
    $modelo_compatible = $_POST['modelo_compatible'] ?? '';
    $tipo_producto = $_POST['tipo_producto'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $min_stock = $_POST['min_stock'] ?? 0;
    $id_proveedor = $_POST['id_proveedor'] ?? 0;
    
    // Recoger el ID del producto (si existe, es una ACTUALIZACIÓN)
    $id_producto = $_POST['id_producto'] ?? null;

    try {
        if ($id_producto) {
            // --- MODO UPDATE (Actualizar) ---
            // Nota: No actualizamos 'nivel_alerta', ¡el trigger lo hará solo!
            $sql = "UPDATE producto SET 
                        id_proveedor = ?,
                        nombre = ?,
                        marca = ?,
                        modelo_compatible = ?,
                        tipo_producto = ?,
                        precio = ?,
                        stock = ?,
                        min_stock = ?
                    WHERE id_producto = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $id_proveedor,
                $nombre,
                $marca,
                $modelo_compatible,
                $tipo_producto,
                $precio,
                $stock,
                $min_stock,
                $id_producto
            ]);

        } else {
            // --- MODO INSERT (Agregar Nuevo) ---
            // Nota: No insertamos 'nivel_alerta', ¡el trigger lo hará solo!
            $sql = "INSERT INTO producto 
                        (id_proveedor, nombre, marca, modelo_compatible, tipo_producto, precio, stock, min_stock) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $id_proveedor,
                $nombre,
                $marca,
                $modelo_compatible,
                $tipo_producto,
                $precio,
                $stock,
                $min_stock
            ]);
        }

        // 3. Redirigir de vuelta a la lista de inventario
        header("Location: inventario.php");
        exit;

    } catch (PDOException $e) {
        // Manejo de errores
        echo "Error al guardar el producto: " . $e->getMessage();
        die();
    }

} else {
    // Si alguien intenta acceder a este archivo directamente
    header("Location: inventario.php");
    exit;
}
?>