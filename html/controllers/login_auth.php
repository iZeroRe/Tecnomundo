<?php
session_start(); 
require '../config/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Obtener las credenciales
    $id_trabajador = $_POST['id_trabajador'] ?? '';
    $contrasena_ingresada = $_POST['contrasena'] ?? '';

    if (empty($id_trabajador) || empty($contrasena_ingresada)) {
        $error = 'Por favor, ingrese ID de empleado y contraseña.';
        include '../login.php';
        exit();
    }

    // Preparar y ejecutar la consulta
    $stmt = $pdo->prepare('SELECT id_trabajador, contrasena, rol FROM trabajador WHERE id_trabajador = ?');
    $stmt->execute([$id_trabajador]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar credenciales
    if ($usuario && $contrasena_ingresada === $usuario['contrasena']) {
        
        // iniciar sesión
        $_SESSION['is_logged_in'] = true;
        $_SESSION['user_id'] = $usuario['id_trabajador'];
        $_SESSION['user_rol'] = $usuario['rol'];

        // 4. Redirigir según el rol
        if ($usuario['rol'] === 'admin') {
            header('Location: ../controllesrs/admin_dashboard.php');
        } elseif ($usuario['rol'] === 'trabajador') {
            header('Location: ../controllesrs/trabajador_dashboard.php');
        } else {
            header('Location: ../controllesrs/default_dashboard.php'); 
        }
        exit(); 
        
    } else {
        $error = 'ID de empleado o contraseña incorrectos.';
        include '../login.php';
    }
} else {
    header('Location: /login.php');
    exit();
}
?>