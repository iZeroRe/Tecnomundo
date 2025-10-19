<?php
// Clase de Conexión a la Base de Datos usando mysqli (Modelo)

class ConexionDB {
    private $host = "db"; //Si tienen extensiones de php veran referencais
    private $usuario = "root";       
    private $password = "root";         
    private $base_datos = "my_project_db"; // Nombre de la BD en Docker
    public $conexion;

    // Manera para hacer las conexiones "obternConexion()"
    public function obtenerConexion() {
        $this->conexion = new mysqli($this->host, $this->usuario, $this->password, $this->base_datos);

        // Verificar si hubo un error de conexión
        if ($this->conexion->connect_error) {
            die("Error de Conexión: " . $this->conexion->connect_error);
        }
        $this->conexion->set_charset("utf8");
        return $this->conexion;
    }
}
?>