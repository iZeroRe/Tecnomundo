<?php 
session_start();
require '../config/conexion.php';

// Veridficacion de inicio de sesion
if(!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true ||$_SESSION['user_rol'] !== 'admin'){
    header('Location: /login.php');
    exit;    
}
//Verificacion de datos por POST
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    die("Acceso no autorizado");
}

// REcibir los datos del form
$id_cliente = $_POST['id_cliente'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$correo = $_POST['correo'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$num_direccion = $_POST['num_direccion'] ?? '';
$id_empresa = $_POST['id_empresa'] ?? '';

// Verificaion de los si hay campos vacios
if(empty($id_cliente) || empty($nombre) || empty($apellido) || empty($telefono) || empty($correo) || empty($direccion) || empty($num_direccion) || empty($id_empresa)){
    //Mensaje de error
    $_SESSION['error_message'] = "Todos los campos son obligatorios";
    header('Location: ../admin/editar_cliente.php?id=' . $id_cliente);
    exit;
}
$pdo->beginTransaction();
// ACtualizacion a la base de datos
try{
$sql = "UPDATE cliente SET 
                nombre = ?, 
                apellido = ?, 
                telefono = ?, 
                correo = ?, 
                direccion = ?, 
                num_direccion = ?, 
                id_empresa = ? 
            WHERE id_cliente = ?";
$stmt = $pdo->prepare($sql);
// Consulta con los datos que se recibieron 
$stmt->execute(([
    $nombre,
    $apellido,
    $telefono,
    $correo,
    $direccion,
    $num_direccion,
    $id_empresa,
    $id_cliente
]));
$pdo->commit();
// Mensaje de exito
$_SESSION['success_message'] = "Actualizacion Exitosa" . htmlspecialchars($nombre) . " " . htmlspecialchars($apellido) . "' actualizado con éxito";
header('Location: ../admin/clientes.php');
exit;
}
catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error_message'] = "Error al actualizar el cliente: " . $e->getMessage();
    header('Location: ../admin/editar_cliente.php?id=' . $id_cliente);
    exit;
}
?>
