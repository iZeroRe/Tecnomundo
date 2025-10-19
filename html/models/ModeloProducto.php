<?php
// Modelo para productos tabla de nuestra BD
// Incluimos el archivo de conexión
include_once '../config/conexion.php'; 

class ModeloProducto {
    private $conn;
    private $tabla = "producto";

    public function __construct() {
        // Inicializamos la conexión al crear el Modelo
        $db_handler = new ConexionDB();
        $this->conn = $db_handler->obtenerConexion();
    }

    // Método para obtener todos los productos del inventario
    public function obtenerTodos() {
        // Consultar los porductos 
        $query = "SELECT id_producto, nombre, marca, modelo_compatible, tipo_producto, precio 
                  FROM " . $this->tabla;
        
        $result = $this->conn->query($query);
        
        // Verifica si hay resultados
        if ($result && $result->num_rows > 0) {
            // Devuelve todos los productos como un array asociativo
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return []; // para casos donde no haya nada array vacio
    }
}
?>