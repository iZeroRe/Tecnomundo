<?php
//Modelo para los trabajadores
//include_once '../config/conexion.php'; Prueba
include_once __DIR__ . '/../config/conexion.php';

class ModeloTrabajador{
    private $conn;
    private $tabla = "trabajador";

    public $id_trabajador;
    public $contrasena;
    public function __construct(){
        // Conexion para obtener datos de la BD
        $db_handler = new ConexionDB();
        $this->conn = $db_handler->obtenerConexion();
    }
    // Verificacion para rol
    public function iniciarSesion(){
        //Obter los datos
    $query = "SELECT id_trabajador, nombre, apellido, contrasena, rol
                From". $this->tabla."
                WHERE id_trabajador = ?
                LIMIT 1";

    if(!($stmt = $this->conn->prepare($query))){
        return false;
    }

    $stmt->bind_param("i", $this->id_trabajador);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    //Verificar al trabajador y verificacion de contraseña
    if($row && $this->contrasena === $row['contrasena']){
        return $row;
        }
    return false;

    }

}
?>