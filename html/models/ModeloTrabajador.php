<?php
// Modelo para los trabajadores: Maneja la comunicación con la BD
include_once __DIR__ . '/../config/conexion.php';

class ModeloTrabajador{
    private $conn;
    private $tabla = "trabajador"; //Tabla

    public $id_trabajador;
    public $contrasena;
    
    public function __construct(){
        $db_handler = new ConexionDB();
        $this->conn = $db_handler->obtenerConexion();
    }

    public function iniciarSesion(){
        // Obtener los datos del trabajador por ID
        $query = "SELECT id_trabajador, nombre, apellido, contrasena, rol
                  FROM " . $this->tabla . " 
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

        // Verificar si el trabajador existe y validar la contraseña
        // Aplicamos trim() a la contraseña ingresada para eliminar espacios accidentales
        $contrasena_ingresada_limpia = trim($this->contrasena);

        if($row && $contrasena_ingresada_limpia === $row['contrasena']){
            return $row;
        }
        return false;
    }
}
// No se usa la etiqueta de cierre 