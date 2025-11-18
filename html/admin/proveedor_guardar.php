<?php
session_start();
require '../config/conexion.php'; 

// --- Bloque de Seguridad ---
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true || $_SESSION['user_rol'] !== 'admin') {
    header('Location: /login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: proveedores.php'); // Redirigir a la lista si no es POST
    exit;
}

// --- 1. Recibir y Determinar Modo ---
// Si existe un ID, estamos en modo edición (viene del campo oculto en proveedor_editar.php)
$id_proveedor = $_POST['id_proveedor'] ?? null; 
$modo = $id_proveedor ? 'editar' : 'agregar';

// 2. Recibir datos principales
$nombre = $_POST['nombre'] ?? '';
$id_empresa = $_POST['id_empresa'] ?? '';
$pieza_accesorio = $_POST['pieza_accesorio'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$correo = $_POST['correo'] ?? '';
$direccion = $_POST['direccion'] ?? '';

// --- 3. Validación de Campos Obligatorios ---
// NOTA: El ID del proveedor se omite en el check de empty() ya que no existe en el modo "agregar"
if (empty($id_empresa) || empty($nombre) || empty($telefono) || empty($correo) || empty($pieza_accesorio)) {
    $_SESSION['error_message'] = "Error: Nombre, Suministra, Teléfono, Correo y Empresa son obligatorios.";
    
    // Redirigir al formulario correcto (editar o agregar)
    if ($modo == 'editar') {
        header('Location: proveedor_editar.php?id=' . $id_proveedor);
    } else {
        header('Location: proveedor_agregar.php');
    }
    exit;
}

// --- 4. Ejecutar Transacción (INSERT/UPDATE) ---
try {
    // Aquí no necesitamos beginTransaction/commit porque es una sola consulta (INSERT/UPDATE), 
    // y asumimos autocommit está ON.
    
    if ($modo == 'agregar') {
        // --- MODO AGREGAR (INSERT) ---
        $sql = "INSERT INTO proveedor (id_empresa, nombre, pieza_accesorio, telefono, correo, direccion) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $params = [
            $id_empresa, 
            $nombre, 
            $pieza_accesorio, 
            $telefono, 
            $correo, 
            $direccion
        ];
        $mensaje_exito = "¡Proveedor '" . htmlspecialchars($nombre) . "' agregado con éxito!";
    } else {
        // --- MODO EDITAR (UPDATE) ---
        $sql = "UPDATE proveedor SET 
                    id_empresa = ?, 
                    nombre = ?, 
                    pieza_accesorio = ?, 
                    telefono = ?, 
                    correo = ?, 
                    direccion = ?
                WHERE id_proveedor = ?";
        $params = [
            $id_empresa, 
            $nombre, 
            $pieza_accesorio, 
            $telefono, 
            $correo, 
            $direccion,
            $id_proveedor 
        ];
        $mensaje_exito = "¡Proveedor '" . htmlspecialchars($nombre) . "' actualizado con éxito!";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // --- 5. Redirección Éxitosa ---
    $_SESSION['success_message'] = $mensaje_exito;
    header('Location: proveedores.php'); // Redirige a la lista
    exit;

} catch (PDOException $e) {
    
    // --- 6. Manejo de Errores ---
    if ($e->getCode() == 23000) { // Código para "Duplicate entry" (Correo/Teléfono ya existe)
        $error_msg = "Error: El correo o el nombre ya está registrado.";
    } else {
        $error_msg = "Error al guardar el proveedor: " . $e->getMessage();
    }
    
    $_SESSION['error_message'] = $error_msg;
    
    // Redirigir al formulario donde ocurrió el error
    if ($modo == 'editar') {
        header('Location: proveedor_editar.php?id=' . $id_proveedor);
    } else {
        header('Location: proveedor_agregar.php');
    }
    exit;
}
?>