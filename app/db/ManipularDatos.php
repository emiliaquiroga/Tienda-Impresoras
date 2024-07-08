<?php


class ManipularDatos{
    private static $instancia;

    private $pdo;
    private function __construct(){
        $conStr = 'mysql:host=' . $_ENV['MYSQL_HOST'] . ';dbname=' . $_ENV['MYSQL_DB'] . ';charset=utf8';
        $usuario = $_ENV['MYSQL_USER'];
        $clave = $_ENV['MYSQL_PASS'];
        try{
            $this->pdo = new PDO($conStr, $usuario, $clave);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $e){
            die("Error de conexión: " . $e->getMessage());
        }
    }
    public static function obtenerInstancia() {
        if (self::$instancia === null) {
            self::$instancia = new ManipularDatos();
        }
        return self::$instancia;
    }

    public function obtenerConexion() {
        return $this->pdo;
    }

    public function prepararConsulta($sql)
    {
        return $this->pdo->prepare($sql);
    }
    public function obtenerUltimoId()
    {
        return $this->pdo->lastInsertId();
    }

}