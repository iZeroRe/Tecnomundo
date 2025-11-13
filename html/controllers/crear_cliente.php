<?php
session_start();
require '../config/conexion.php'; // Incluimos la conexión a la BD

// Verificar al admin
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true || $_SESSION['user_rol'] !== 'admin') {
    header('Location: /login.php');
    exit;
}
// Verificamos que los datos se hayan enviado por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso no autorizado.");
}
// Recibimos todos los datos del formulario
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$correo = $_POST['correo'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$num_direccion = $_POST['num_direccion'] ?? '';
$id_empresa = $_POST['id_empresa'] ?? '';

// Verificacion para campos vacios
if (empty($nombre) || empty($apellido) || empty($telefono) || empty($correo) || empty($direccion) || empty($num_direccion) || empty($id_empresa)) {
    // Si un campo obligatorio falta, guardamos un error y redirigimos
    $_SESSION['error_message'] = "Error: Todos los campos son obligatorios.";
    header('Location: ../admin/nuevo_cliente.php');
    exit;
}

try {
    // SQL para el nuevo cliente 
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

    // Mensjae de salida
    $_SESSION['success_message'] = "¡Cliente '" . htmlspecialchars($nombre) . " " . htmlspecialchars($apellido) . "Tenemos nuevo cliente :)";
    
    // Regreso a clientes.php
    header('Location: ../admin/clientes.php');
    exit;

} catch (PDOException $e) {
    // Si algo fallo guardamos el error
    $_SESSION['error_message'] = "No se puede guardar el cliente, verifique los datos" . $e->getMessage();
    
    // Redirigimos de vuelta al formulario
    header('Location: ../admin/nuevo_cliente.php');
    exit;
}
?>