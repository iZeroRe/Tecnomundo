<?php
session_start();
require '../config/conexion.php';

//Verificamos que el usuario sea admin
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true || $_SESSION['user_rol'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

// Verificamos que los datos se hayan enviado por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso no autorizado.");
}

// Resibe los datos y valida
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';
$rol = $_POST['rol'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$correo = $_POST['correo'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$num_direccion = $_POST['num_direccion'] ?? '';
$especialidad = $_POST['especialidad'] ?? '';
$id_empresa = $_POST['id_empresa'] ?? '';

// Campos vacios
if (empty($nombre) || empty($apellido) || empty($contrasena) || empty($rol) || empty($telefono) || empty($correo) || empty($id_empresa)) {
    $_SESSION['error_message'] = "Error: Faltan campos obligatorios.";
    header('Location: ../admin/nuevo_trabajador.php');
    exit;
}
// Insertar en la BD
try {
    // Insertet de los daots
    $sql = "INSERT INTO trabajador 
                (nombre, apellido, contrasena, rol, telefono, correo, direccion, num_direccion, especialidad, id_empresa) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    
    // Ejecutamos la consulta con los datos
    $stmt->execute([
        $nombre,
        $apellido,
        $contrasena,
        $rol,
        $telefono,
        $correo,
        $direccion,
        $num_direccion,
        $especialidad,
        $id_empresa
    ]);

    // Mensaje de exito
    $_SESSION['success_message'] = "¡Trabajador '" . htmlspecialchars($nombre) . "' creado con éxito!";
    header('Location: ../admin/trabajadores.php');
    exit;

} catch (PDOException $e) {
    // Manejo de Errores 
    if ($e->getCode() == 23000) {
        $_SESSION['error_message'] = "Error: El correo electrónico '" . htmlspecialchars($correo) . "' ya está registrado.";
    } else {
        $_SESSION['error_message'] = "Error al guardar el trabajador: " . $e->getMessage();
    }
    
    header('Location: ../admin/nuevo_trabajador.php');
    exit;
}
?>