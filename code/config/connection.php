<?php
class Connection {
    private $host = 'localhost';
    private $dbname = 'tecnomundo';
    private $username = 'root';
    private $password = '';     

    public function connect(): PDO {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            return new PDO($dsn, $this->username, $this->password, $options);

        } catch (Throwable $th) {
            echo "Error en la conexión: " . $th->getMessage();
            exit();
        }
    }
}
