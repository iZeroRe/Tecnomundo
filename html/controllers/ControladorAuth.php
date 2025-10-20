<?php
include_once __DIR__ . '/../models/ModeloTrabajador.php';
// inclusion del modelotrabajador para conectar
include_once '../models/ModeloTrabajador.php';
// Error en caso de Login
$error = ''; 

class ControladorAuth{
    private $modelo_trabajador;
    public function __construct(){
        //Inicializacion para modelo trabajador conexion con DB
    $this->modelo_trabajador = new ModeloTrabajador();
     }
     public function iniciarSesion(){
        global $error;

        //En caso de que ya este en sesion se redirige
        if(isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
            header("Location: index.php");
            exit();
            }
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->modelo_trabajador->id_trabajador = isset($_POST['id_trabajador']) ? $_POST['id_trabajador'] : '';
            $this->modelo_trabajador->contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';

            if (empty($this->modelo_trabajador->id_trabajador) || empty($this->modelo_trabajador->contrasena)) {
                $error = "Por favor, ingresa tu ID y contraseña.";
            }

        else{
            $datos_usuario = $this->modelo_trabajador->iniciarSesion();

            if ($datos_usuario) {
                    // Autenticación Exitosa: Crear Sesión
                    session_regenerate_id();
                    $_SESSION['is_logged_in'] = true;
                    $_SESSION['id_trabajador'] = $datos_usuario['id_trabajador'];
                    $_SESSION['user_name'] = $datos_usuario['nombre'] . ' ' . $datos_usuario['apellido'];
                    $_SESSION['user_role'] = $datos_usuario['rol']; // La clave para la seguridad
                    
                    // Redirigir al index.php
                    header('Location: ../index.php'); 
                    exit();
        }
            else {
                    $error = "ID de empleado o contraseña incorrectos.";
                }
        }
     }
        include_once '../views/login.php';
}
        public function cerrarSesion() {
        session_start();
        session_unset(); 
        session_destroy(); 
        header('Location: ../login.php'); 
        exit();
    }
}
// Decide si ejecutar iniciarSesion o cerrarSesion basándose en la URL, enrutamiento.
$controlador = new ControladorAuth();
$action = isset($_GET['action']) ? $_GET['action'] : 'iniciarSesion';

if ($action == 'cerrarSesion') {
    $controlador->cerrarSesion();
} else {
    $controlador->iniciarSesion();
}
?>