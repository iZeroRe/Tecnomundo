<?php
// Clase de Conexión a la Base de Datos usando mysqli (Modelo)

class ConexionDB {
    private $host = "db"; //Si tienen extensiones de php veran referencais
    private $usuario = "root";       
    private $password = "root";         
    private $base_datos = "tecnomundo"; // Nombre de la BD en Docker prubea 
    public $conexion;

    // Manera para hacer las conexiones "obternConexion()"
    public function obtenerConexion() {
        $this->conexion = new mysqli($this->host, $this->usuario, $this->password, $this->base_datos);

        // Verificar si hubo un error de conexión
        if ($this->conexion->connect_error) {
            die("Error de Conexión: " . $this->conexion->connect_error);
        }
        if (!$this->conexion->select_db($this->base_datos)) {
            die("Error al seleccionar la base de datos: " . $this->base_datos);
        }
        $this->conexion->set_charset("utf8");
        return $this->conexion;
    }
}
