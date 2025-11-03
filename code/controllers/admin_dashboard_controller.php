<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    $_SESSION['login_error'] = "Acceso denegado. Debes iniciar sesión como administrador.";
    header("Location: ../index.php");
    exit();
}

class Dashboard_controller {

    public function __construct() {

    }

    public function Dashboard(): void {
        require_once "../views/dashboard_admin/header.php";
        require_once "../views/dashboard_admin/homepage.php"; 
        require_once "../views/dashboard_admin/footer.php";
    }
}

$dashboard = new Dashboard_controller();
$dashboard->Dashboard();