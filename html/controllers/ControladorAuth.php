<?php
// CONTROLADOR: Maneja la lógica de Autenticación
include_once __DIR__ . '/../models/ModeloTrabajador.php'; 

$error = ''; // Variable global para pasar mensajes de error a la Vista

class ControladorAuth {
    private $modelo_trabajador;
    
    public function __construct() {
        // Inicialización del Modelo
        $this->modelo_trabajador = new ModeloTrabajador();
    }

    public function iniciarSesion() {
        global $error;
        
        // Redirigir si el usuario ya está logueado
        if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
            header('Location: /index.php'); 
            exit();
        }

        // Procesar el formulario (Método POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $this->modelo_trabajador->id_trabajador = isset($_POST['id_trabajador']) ? $_POST['id_trabajador'] : '';
            $this->modelo_trabajador->contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';

            if (empty($this->modelo_trabajador->id_trabajador) || empty($this->modelo_trabajador->contrasena)) {
                 $error = "Por favor, ingresa tu ID y contraseña.";
            } else {

                $datos_usuario = $this->modelo_trabajador->iniciarSesion();

                if ($datos_usuario) {
                    //Autenticación Exitosa: Crear Sesión
                    //Prueba session_regenerate_id(); // Opcional, pero buena práctica de seguridad
                    $_SESSION['is_logged_in'] = true;
                    $_SESSION['id_trabajador'] = $datos_usuario['id_trabajador'];
                    $_SESSION['user_name'] = $datos_usuario['nombre'] . ' ' . $datos_usuario['apellido'];
                    $_SESSION['user_role'] = $datos_usuario['rol']; 
                    
                    // Redirigir al Tablero (Ruta absoluta segura)
                    header('Location: /index.php'); 
                    exit(); // ¡CRUCIAL! Asegura que no se ejecute más código.
                } else {
                    $error = "ID de empleado o contraseña incorrectos.";
                }
            }
        }
    }
    
    public function cerrarSesion() {
        session_start();
        session_unset(); 
        session_destroy(); 
        header('Location: /login.php'); // Redirige al login de la raíz
        exit();
    }
}

$controlador = new ControladorAuth();
$action = isset($_GET['action']) ? $_GET['action'] : 'iniciarSesion';

if ($action == 'cerrarSesion') {
    $controlador->cerrarSesion();
} else {
    $controlador->iniciarSesion();
}

// Si la lógica no redirigió (porque falló el login), cargamos la vista del formulario.
include_once __DIR__ . '/../views/login.php';