<?php

class Venta{
    public $id;
    public $email_usuario;
    public $marca;
    public $tipo;
    public $modelo;
    public $stock;
    public $cantidad;
    public $precio_total;
    public $numero_pedido;
    public $imagen;
    public $fecha;
    private $tabla = 'ventas';

    public function altaVenta(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $this->verificarCamposVenta();
        $suficienteStock = $this->verificarStock($this->marca, $this->tipo, $this->modelo, $this->cantidad);
        if($suficienteStock){
            $precioProducto = $this->precioVenta($this->marca, $this->tipo, $this->modelo);
            $this->precio_total = $precioProducto * $this->cantidad;
            $query = "INSERT INTO ventas (email_usuario, marca, tipo, modelo, cantidad, precio_total, numero_pedido) VALUES (:email_usuario, :marca, :tipo, :modelo, :cantidad, :precio_total, :numero_pedido)";
            $consulta = $objAccesoDatos->prepararConsulta($query);
            $consulta->bindValue(':email_usuario', $this->email_usuario, PDO::PARAM_STR_CHAR);
            $consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
            $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
            $consulta->bindValue(':modelo', $this->modelo, PDO::PARAM_STR);
            $consulta->bindValue(':cantidad', $this->cantidad, PDO::PARAM_INT);
            $consulta->bindValue(':precio_total', $this->precio_total, PDO::PARAM_INT);
            $consulta->bindValue(':numero_pedido', $this->numero_pedido, PDO::PARAM_STR_CHAR);
            $consulta->execute();
            Producto::descontarStock($this->marca, $this->tipo, $this->modelo, $this->cantidad);

            return $objAccesoDatos->obtenerUltimoId();
        }else{
            die(throw new Exception("Sin stock"));
        }
        
    }

    private function verificarCamposVenta() {
        $camposRequeridos = ['email_usuario', 'marca', 'tipo', 'modelo', 'cantidad'];
        foreach ($camposRequeridos as $campo) {
            if (empty($this->$campo)) {
                throw new Exception("El campo $campo esta incompleto.");
            }
        }
    }
    public function verificarStock($marca, $tipo, $modelo, $cantidad){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT stock FROM productos WHERE marca = :marca AND tipo = :tipo AND modelo = :modelo AND stock >= :cantidad";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->bindValue(':marca', $marca, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':modelo', $modelo, PDO::PARAM_STR);
        $consulta->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
        $consulta->execute();

        $producto = $consulta->fetch(PDO::FETCH_ASSOC);

        if($producto && $producto['stock'] >= $cantidad){
            return true;
        }else{
            return false;
        }
    }

    public function precioVenta($marca, $tipo, $modelo){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT precio FROM productos WHERE marca = :marca AND tipo = :tipo AND modelo = :modelo";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->bindValue(':marca', $marca, PDO::PARAM_STR);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->bindValue(':modelo', $modelo, PDO::PARAM_STR);
        $consulta->execute();
        $producto = $consulta->fetch(PDO::FETCH_ASSOC);
        if($producto){
            return $producto['precio'];
        }else{
            throw new Exception("Precio no encotrado");
        }
        
    }


    public static function GuardarImagenVenta($ruta, $uploadedFile, $marca, $tipo, $modelo, $email, $fecha) {
        if (!file_exists($ruta)) {
            mkdir($ruta, 0777, true);
        }

        $filename = $marca . "_" . $tipo ."_". $modelo."_".$email."_".$fecha."." . pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $filepath = $ruta . DIRECTORY_SEPARATOR . $filename;

        $uploadedFile->moveTo($filepath);

        return $filename;
    }
    public function generarNumeroVenta() {
        $ultimoId = ManipularDatos::obtenerInstancia()->obtenerUltimoId();
    
        // Verificación adicional del ID
        if (!is_numeric($ultimoId) || $ultimoId < 0) {
            $ultimoId = 0; 
        }
        
        $numero = (int)$ultimoId + 1;
        $random = rand(0, 99);  
        $numeroStr = str_pad($numero, 2, '0', STR_PAD_LEFT);
        $codigo = str_pad($random, 2, '0', STR_PAD_LEFT) . $numeroStr;
        
        return $codigo;
    }

    public function verificarNumero($objAccesoDatos){
        $query = "SELECT COUNT(*) as contador FROM ventas WHERE numero_pedido = :numero_pedido";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->bindValue(':numero_pedido', $this->numero_pedido, PDO::PARAM_STR_CHAR);
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        if($resultado['contador'] === 0){
            return false;
        }else{
            return true;
        }
    }
    public function modificarVenta($objAccesoDatos){
        $numeroExiste = $this->verificarNumero($objAccesoDatos);
        if($numeroExiste){
            $query = "UPDATE ventas SET email_usuario = :email_usuario, marca = :marca, tipo = :tipo, modelo = :modelo, cantidad = :cantidad WHERE numero_pedido = :numero_pedido ";
            $consulta = $objAccesoDatos->prepararConsulta($query);
            try{
                $consulta->bindValue(':email_usuario', $this->email_usuario, PDO::PARAM_STR_CHAR);
                $consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
                $consulta->bindValue(':tipo', $this->tipo, PDO::PARAM_STR);
                $consulta->bindValue(':modelo', $this->modelo, PDO::PARAM_STR);
                $consulta->bindValue(':cantidad', $this->cantidad, PDO::PARAM_INT);
                $consulta->bindValue(':numero_pedido', $this->numero_pedido, PDO::PARAM_STR_CHAR);
                $consulta->execute();
                return "Venta modificada exitosamente";
            }catch(Exception $e){
                throw new Exception("ERROR AL MODIFICAR LA VENTA." . $e->getMessage());
            }
        }else{
            die("No existe el numero del pedido, intente nuevamente");
        }


    }

    public function ventaPorProducto($tipo){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT id, email_usuario, marca, tipo, modelo, cantidad, fecha, numero_pedido FROM ventas WHERE tipo = :tipo";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $consulta->execute();
        
        $venta = $consulta->fetchAll(PDO::FETCH_ASSOC);
        return $venta;
    }

    public function ventaPorUsuario($usuario){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT * FROM ventas WHERE email_usuario = :email_usuario";
        $consulta =$objAccesoDatos->prepararConsulta($query);
        $consulta->bindValue(':email_usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();
        
        $venta = $consulta->fetchAll(PDO::FETCH_ASSOC);
        return $venta;
    }

    #E- ruta: “/ventas/ingresos” El listado de ingresos (ganancia de las ventas) por día de una fecha ingresada. Si no se
    #ingresa una fecha, se muestran los ingresos de todos los días.
    public function ingresoDeVentas($fecha = null){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $totalVentas = 0;
        if(!isset($fecha) || $fecha === null){
            $query = "SELECT SUM(precio_total) as total_venta FROM ventas";
            $consulta = $objAccesoDatos->prepararConsulta($query);
        }else{
            $query = "SELECT SUM(precio_total) as total_venta FROM ventas WHERE DATE(fecha) = :fecha";
            $consulta = $objAccesoDatos->prepararConsulta($query);
            $consulta->bindValue(':fecha', $fecha, PDO::PARAM_STR);
        }
        $consulta->execute();
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        if($resultado && isset($resultado['total_venta'])){
            $totalVentas = $resultado['total_venta'];
        }
        return $totalVentas;
    }

    public function traerProductoMasVendido(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT marca, tipo, modelo, SUM(cantidad) as total_unidades 
                    FROM ventas 
                    GROUP BY marca, tipo, modelo 
                    ORDER BY total_unidades DESC 
                    LIMIT 1";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->execute();
        $productoMasVendido = $consulta->fetch(PDO::FETCH_ASSOC);
        return $productoMasVendido;
    }

    public function leerVenta(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $query = "SELECT * FROM ventas";
        $consulta = $objAccesoDatos->prepararConsulta($query);
        $consulta->execute();
        return $consulta;
    }
    public function exportarArchivoCSV(){
        $objAccesoDatos = ManipularDatos::obtenerInstancia();
        $fh = fopen('php://temp', 'w+'); //file handler

        if ($fh === false) {
            throw new Exception("Error al crear archivo CSV.");
        }
        
        fputcsv($fh, ['id', 'email_usuario', 'marca', 'tipo', 'modelo', 'cantidad', 'precio_total', 'fecha', 'numero_pedido']);
        $ventas = new Venta();
        $consulta = $ventas->leerVenta();
        $listaVentas = $consulta->fetchAll(PDO::FETCH_ASSOC);
        foreach($listaVentas as $venta){
            fputcsv($fh, $venta);
        }
        $csv = stream_get_contents($fh, -1, 0);
        fclose($fh);
        
        if ($csv === false) {
            throw new Exception("Error al leer el archivo CSV.");
        }
        return $csv;
    }


}
