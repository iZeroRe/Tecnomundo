<?php
require_once '../config/Connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_trabajador = $_POST['id_trabajador'];
    $contrasena = $_POST['contrasena'];

    try {
        $connection = new Connection();
        $pdo = $connection->connect();

        $sql = "SELECT * FROM trabajador WHERE id_trabajador = :id_trabajador LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id_trabajador' => $id_trabajador]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $contrasena === $user['contrasena']) {
            
            $_SESSION['user_id']   = $user['id_trabajador'];
            $_SESSION['nombre']    = $user['nombre'];
            $_SESSION['rol']       = $user['rol'];
            
            if ($user['rol'] === 'admin') {
                header("Location: ../controllers/admin_dashboard_controller.php");
            } elseif ($user['rol'] === 'trabajador') {
                header("Location: ../controllers/trabajador_dashboard_controller.php");
            } else {
                echo "Rol no autorizado";
            }
            exit();
        } else {
            $error_message = 'Credenciales Incorrectas';
            header("Location: ../index.php?error=$error_message");
            exit();
        }

    } catch (Throwable $th) {
        $error_message = "Error en la conexión: " . $th->getMessage();
        header("Location: ../index.php?error=$error_message");
        exit();
    }
}
