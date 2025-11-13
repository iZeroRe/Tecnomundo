<?php
session_start();
require '../config/conexion.php';

// Verificamos que el usuario sea admin
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true || $_SESSION['user_rol'] !== 'admin') {
    header('Location: /login.php');
    exit;
}

// Verificamos que los datos se hayan enviado por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso no autorizado.");
}

// REcibir los datos
$id_trabajador = $_POST['id_trabajador'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$rol = $_POST['rol'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$correo = $_POST['correo'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$num_direccion = $_POST['num_direccion'] ?? '';
$especialidad = $_POST['especialidad'] ?? '';
$id_empresa = $_POST['id_empresa'] ?? '';
//La contrasela puede estar sin llenar a menos de que la quieran cambiar
$contrasena = $_POST['contrasena'] ?? '';

// Verificación de campos (NOTA: no validamos la contraseña, porque puede ir vacía)
if (empty($id_trabajador) || empty($nombre) || empty($apellido) || empty($rol) || empty($telefono) || empty($correo) || empty($id_empresa)) {
    $_SESSION['error_message'] = "Error: Faltan campos obligatorios (excepto contraseña).";
    header('Location: ../admin/editar_trabajador.php?id=' . $id_trabajador);
    exit;
}

$pdo->beginTransaction();
try {
    // Preparamos de la consulta
    $params = [
        $nombre,
        $apellido,
        $rol,
        $telefono,
        $correo,
        $direccion,
        $num_direccion,
        $especialidad,
        $id_empresa
    ];

    // Preparamos la consulta SQL base
    $sql = "UPDATE trabajador SET 
                nombre = ?, 
                apellido = ?, 
                rol = ?, 
                telefono = ?, 
                correo = ?, 
                direccion = ?, 
                num_direccion = ?, 
                especialidad = ?, 
                id_empresa = ? ";
    // Si el usuario SÍ escribió una nueva contraseña
    if (!empty($contrasena)) {
        $sql .= ", contrasena = ? "; //Se añade al UPDATE
        $params[] = $contrasena;
    }

    //TErminamos la consulta con WHERE
    $sql .= " WHERE id_trabajador = ?";
    // Añadimos el ID del trabajador al final
    $params[] = $id_trabajador;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $pdo->commit();
    
    $_SESSION['success_message'] = "¡Trabajador '" . htmlspecialchars($nombre) . "' actualizado con éxito!";
    header('Location: ../admin/trabajadores.php');
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    if ($e->getCode() == 23000) {
        $_SESSION['error_message'] = "Error: El correo electrónico '" . htmlspecialchars($correo) . "' ya existe.";
    } else {
        $_SESSION['error_message'] = "Error al actualizar el trabajador: " . $e->getMessage();
    }
    header('Location: ../admin/editar_trabajador.php?id=' . $id_trabajador);
    exit;
}
?>