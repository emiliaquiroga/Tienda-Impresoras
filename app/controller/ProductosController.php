<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once '../app/models/Producto.php';

class ProductosController {

    public function altaProducto(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        $producto = new Producto();
        $producto->marca = $data['marca'];
        $producto->precio = $data['precio'];
        try {
            $producto->tipo = $this->verificarTipo($data['tipo']);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
        $producto->modelo = $data['modelo'];
        $producto->color = $data['color'];
        $producto->stock = $data['stock'];


        if (isset($uploadedFiles['imagen'])) {
            $imagen = $uploadedFiles['imagen'];
            if ($imagen->getError() === UPLOAD_ERR_OK) {
                $filename = Producto::GuardarImagenProducto("../ImagenesDeProductos/2024", $imagen, $producto->marca, $producto->tipo);
                $producto->imagen = $filename;
            }
        }

        $producto->altaProducto();

        $response->getBody()->write(json_encode(['mensaje' => "Producto creado o actualizado exitosamente!"]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function consultarProducto(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $marca = $data['marca'];
        $tipo = $data['tipo'];
        $color = $data['color'];

        $producto = new Producto();
        $result = $producto->consultar($marca, $tipo, $color);
        

        if ($result) {
            $response->getBody()->write(json_encode(["mensaje" => "existe"]));
        } else {
            $response->getBody()->write(json_encode(["mensaje" => "no existe el tipo o la marca"]));
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function verificarTipo($tipo){
        if($tipo != 'impresora' && $tipo != 'cartucho'){
            throw new Exception("El tipo de producto debe ser 'impresora' o 'cartucho'!");
        }
        return $tipo;
    }
    
    public function productosVendidos(Request $request, Response $response, $args){
        $data = $request->getQueryParams();
        if(!isset($data['fecha'])){
            $fecha = null;
            $fecha = date('Y-m-d', strtotime('-1 day'));
        }else{
            $fecha = $data['fecha'];
        }
        
        $producto = new Producto();
        try {
            $cantidadVendida = $producto->productosVendidosPorFecha($fecha);
            $response->getBody()->write(json_encode(['cantidad_vendida' => $cantidadVendida]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function productosEntreDosPrecios(Request $request, Response $response, $args){
        $data = $request->getQueryParams();
        if(!isset($data['min']) || !isset($data['max'])){
            $response->getBody()->write(json_encode(['error' => 'Por Favor, ingrese parametro de valor minimo y malor maximo']));
            $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $min = filter_var($data['min'], FILTER_VALIDATE_INT);
        $max = filter_var($data['max'], FILTER_VALIDATE_INT);

        if ($min === false || $max === false) {
            $response->getBody()->write(json_encode(['error' => 'Error en los parámetros']));
            $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    
        $producto = new Producto();

        try {
            $productos = $producto->productosEntreDosValores($min, $max);
            $response->getBody()->write(json_encode(['productos' => $productos]));
            if(count($productos) == 0){
                $mensaje = "No se encontraron productos entre los valores ingresados. Proba con otros valores.";
                $response->getBody()->write(json_encode(['productos' => $mensaje]));
            }
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
        return $response;
        
    }

}
