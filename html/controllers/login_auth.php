<?php
// Inicia la sesión
session_start(); 
require '../config/conexion.php'; // Asegúrate de que $pdo esté disponible

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Obtener las credenciales
    $id_trabajador = $_POST['id_trabajador'] ?? '';
    $contrasena_ingresada = $_POST['contrasena'] ?? '';

    if (empty($id_trabajador) || empty($contrasena_ingresada)) {
        $error = 'Por favor, ingrese ID de empleado y contraseña.';
        include '../login.php';
        exit();
    }

    // 2. Preparar y ejecutar la consulta
    $stmt = $pdo->prepare('SELECT id_trabajador, contrasena, rol FROM trabajador WHERE id_trabajador = ?');
    $stmt->execute([$id_trabajador]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Verificar credenciales
    if ($usuario && $contrasena_ingresada === $usuario['contrasena']) {
        
        // Credenciales correctas, iniciar sesión
        $_SESSION['is_logged_in'] = true;
        $_SESSION['user_id'] = $usuario['id_trabajador'];
        $_SESSION['user_rol'] = $usuario['rol'];

        // 4. Redirigir según el rol, incluyendo un 'else' para cualquier otro rol
        if ($usuario['rol'] === 'admin') {
            header('Location: ../controllesrs/admin_dashboard.php');
        } elseif ($usuario['rol'] === 'trabajador') {
            header('Location: ../controllesrs/trabajador_dashboard.php');
        } else {
            // **AÑADIDO: Manejo de rol desconocido o inesperado** 💡
            // Si el rol existe pero no es 'admin' ni 'trabajador', lo mandamos a una página de error o predeterminada
            header('Location: ../controllesrs/default_dashboard.php'); 
        }
        exit(); // Termina la ejecución después de cualquier redirección exitosa.
        
    } else {
        // **AQUÍ CAE SI EL USUARIO NO EXISTE O LA CONTRASEÑA ES INCORRECTA** 🚨
        // Credenciales incorrectas, mostramos error y volvemos a la página de login.
        $error = 'ID de empleado o contraseña incorrectos.';
        include '../login.php';
        // No es necesario exit() aquí si el include de login.php se encarga de mostrar el formulario y finalizar.
    }
} else {
    // Si no es POST
    header('Location: /login.php');
    exit();
}
?>