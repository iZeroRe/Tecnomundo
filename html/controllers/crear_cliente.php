<?php
session_start();
require '../config/conexion.php'; // Incluimos la conexión a la BD

// --- 1. Verificación de Seguridad ---

// Verificamos que el usuario sea admin
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true || $_SESSION['user_rol'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

// Verificamos que los datos se hayan enviado por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso no autorizado.");
}

// --- 2. Recibir y Validar Datos ---
// Recibimos todos los datos del formulario
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$correo = $_POST['correo'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$num_direccion = $_POST['num_direccion'] ?? '';
$id_empresa = $_POST['id_empresa'] ?? '';

// Verificación simple de que los campos no estén vacíos
if (empty($nombre) || empty($apellido) || empty($telefono) || empty($correo) || empty($direccion) || empty($num_direccion) || empty($id_empresa)) {
    // Si un campo obligatorio falta, guardamos un error y redirigimos
    $_SESSION['error_message'] = "Error: Todos los campos son obligatorios.";
    header('Location: ../admin/nuevo_cliente.php');
    exit;
}

// --- 3. Insertar en la Base de Datos ---

try {
    // Preparamos la consulta SQL para insertar el nuevo cliente
    $sql = "INSERT INTO cliente (nombre, apellido, telefono, correo, direccion, num_direccion, id_empresa) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    // Ejecutamos la consulta con los datos recibidos
    $stmt->execute([
        $nombre,
        $apellido,
        $telefono,
        $correo,
        $direccion,
        $num_direccion,
        $id_empresa
    ]);

    // --- 4. Redirección Éxitosa ---
    // Si todo salió bien, creamos un mensaje de éxito
    $_SESSION['success_message'] = "¡Cliente '" . htmlspecialchars($nombre) . " " . htmlspecialchars($apellido) . "' registrado con éxito!";
    
    // Redirigimos de vuelta a la lista de clientes
    header('Location: ../admin/clientes.php');
    exit;

} catch (PDOException $e) {
    // --- 5. Manejo de Errores ---
    // Si algo falló (ej. correo duplicado), guardamos el error
    $_SESSION['error_message'] = "Error al guardar el cliente: " . $e->getMessage();
    
    // Redirigimos de vuelta al formulario
    header('Location: ../admin/nuevo_cliente.php');
    exit;
}
?>