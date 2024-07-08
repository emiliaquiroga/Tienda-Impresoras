<?php

class Producto{
    public $marca;
    public $precio;
    public $tipo;
    public $modelo;
    public $color;
    public $stock;
    public $imagen;

    private $tabla = 'productos';

    public function altaProducto(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $existe = $this->verificarRepeticion($objAccesoDatos);
        
        if ($existe) {
            $query = "UPDATE productos SET precio = :precio, stock = stock + :stock WHERE marca = :marca AND tipo = :tipo";
            $consulta = $objAccesoDatos->prepararConsulta($query);
            $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
            $consulta->bindValue(':stock', $this->stock, PDO::PARAM_INT);
            $consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
            $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
        } else {
            
            $query = "INSERT INTO productos (marca, precio, tipo, modelo, color, stock) VALUES (:marca, :precio, :tipo, :modelo, :color, :stock)";
            $consulta = $objAccesoDatos->prepararConsulta($query);
            $consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
            $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
            $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
            $consulta->bindValue(':modelo', $this->modelo, PDO::PARAM_STR);
            $consulta->bindValue(':color', $this->color, PDO::PARAM_STR);
            $consulta->bindValue(':stock', $this->stock, PDO::PARAM_INT);

        }
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    private function verificarRepeticion($objAccesoDatos){
        $query = "SELECT * FROM productos WHERE marca = :marca AND tipo = :tipo";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->rowCount() > 0;
    }
    public static function GuardarImagenProducto($ruta, $uploadedFile, $marca, $tipo) {
        if (!file_exists($ruta)) {
            mkdir($ruta, 0777, true);
        }

        $filename = $marca . "_" . $tipo ."." . pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $filepath = $ruta . DIRECTORY_SEPARATOR . $filename;

        $uploadedFile->moveTo($filepath);

        return $filename;
    }
    public function leerProductos(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT id, nombre_producto, tipo_producto, stock, precio FROM " .$this->tabla;
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->execute();
        return $consulta;
    }

    public function consultar($marca, $tipo, $color){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT * FROM ".$this->tabla." WHERE marca = :marca AND tipo = :tipo AND color = :color";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->bindValue(':marca', $marca, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':color', $color, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->rowCount() > 0;
    }

    public static function descontarStock($marca, $tipo, $modelo, $cantidad){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "UPDATE productos SET stock = stock - :cantidad WHERE marca = :marca AND tipo = :tipo AND modelo = :modelo";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->bindvalue(':cantidad', $cantidad, PDO::PARAM_INT);
        $consulta->bindvalue(':marca', $marca, PDO::PARAM_STR);
        $consulta->bindvalue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindvalue(':modelo', $modelo, PDO::PARAM_STR);
        $consulta->execute();
    }

    public function productosVendidosPorFecha($fecha = null){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $cantidadVendida = 0;
        if($fecha === null){
            $fecha = date('Y-m-d', strtotime('-1 day'));
        }
        $query = "SELECT SUM(cantidad) as cantidad_total FROM ventas WHERE DATE(fecha) = :fecha";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR_CHAR);
        $consulta->execute();

        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        if ($resultado && $resultado['cantidad_total']) {
            $cantidadVendida = $resultado['cantidad_total'];
        }

        return $cantidadVendida;
    }

    public function productosEntreDosValores($min, $max){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT * FROM productos WHERE precio BETWEEN :min AND :max";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->bindValue(':min', $min, PDO::PARAM_INT);
        $consulta->bindValue(':max', $max, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }



}