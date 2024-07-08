<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
require_once '../app/models/Venta.php';
require_once '../app/models/Producto.php';

class VentaController{
    public function altaNuevaVenta(Request $request, Response $response, $args){
        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        $venta = new Venta();
        $venta->email_usuario = strstr($data['email_usuario'], '@', true);
        $venta->marca = $data['marca'];
        $venta->tipo = $data['tipo'];
        $venta->modelo = $data['modelo'];
        $venta->cantidad = $data['cantidad'];
        $venta->numero_pedido = $venta->generarNumeroVenta();
        if (isset($uploadedFiles['imagen'])) {
            $imagen = $uploadedFiles['imagen'];
            if ($imagen->getError() === UPLOAD_ERR_OK) {
                $filename = Venta::GuardarImagenVenta("../ImagenesDeVenta/2024", $imagen, $venta->marca, $venta->tipo, $venta->modelo, $venta->email_usuario, $venta->fecha);
                $venta->imagen = $filename;
            }
        }
        try {
            $venta->altaVenta();
            $response->getBody()->write(json_encode(['mensaje' => "Venta exitosa!"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
    public function verificarVenta(Request $request, Response $response, $args){
        $data = $request->getParsedBody();
        $marca = $data['marca'];
        $tipo = $data['tipo'];
        $modelo = $data['modelo'];
        $cantidad = $data['cantidad'];

        $venta = new Venta();
        $result = $venta->verificarStock($marca, $tipo, $modelo, $cantidad);
        if ($result) {
            $response->getBody()->write(json_encode(["mensaje" => "VENTA EXITOSA!"]));
        } else {
            $response->getBody()->write(json_encode(["mensaje" => "No se pudo realizar la venta!"]));
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function traerVentaPorProducto(Request $request, Response $response, $args){
        $data = $request->getQueryParams();
        $tipo = $data['tipo'];
        $venta = new Venta();
        if(isset($data['tipo']) && $data['tipo'] != null){
            try{
                echo "el listado de ventas del tipo ".$tipo." es: ";
                $listadoVentas = $venta->ventaPorProducto($tipo);
                $response->getBody()->write(json_encode($listadoVentas));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    
            }catch(Exception $e){
                $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        }else{
            $response->getBody()->write(json_encode(['error' => 'Se espera recibir tipo de producto']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function traerVentasPorUsuario(Request $request, Response $response, $args){
        $data = $request->getQueryParams();
        $usuario = isset($data['email_usuario']) ? $data['email_usuario'] : null;

        if($usuario){
            $venta = new Venta();
            try{
                echo "el listado de ventas del ".$usuario." es: ";
                $listadoVentas = $venta->ventaPorUsuario($usuario);
                $response->getBody()->write(json_encode($listadoVentas));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    
            }catch(Exception $e){
                $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        }else{
            $response->getBody()->write(json_encode(['error' => 'Se espera recibir un usuario']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function traerGananciasPorVentas(Request $request, Response $response, $args){
        $data = $request->getQueryParams();
        if(!isset($data['fecha']) || empty($data['fecha'])){
            $fecha = null;
        }else{
            $fecha = $data['fecha'];
        }

        $venta = new Venta();
        try{
            $gananciasVentas = $venta->ingresoDeVentas($fecha);
            $response->getBody()->write(json_encode(['total_ventas'=> $gananciasVentas]));
            return$response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }catch(Exception $e){
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

    }

    public function obtenerProductoMasVendido(Request $request, Response $response, $args){
        $venta = new Venta();
        try {
            $productoMasVendido = $venta->traerProductoMasVendido();
            if ($productoMasVendido) {
                $response->getBody()->write(json_encode($productoMasVendido));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                $response->getBody()->write(json_encode(['mensaje' => "No se encontraron ventas."]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    public function modificarVenta(Request $request, Response $response, $args){
        $data = $request->getParsedBody();

        $venta = new Venta();
        $venta->email_usuario = $data['email_usuario'];
        $venta->marca = $data['marca'];
        $venta->tipo = $data['tipo'];
        $venta->modelo = $data['modelo'];
        $venta->cantidad= $data['cantidad'];
        $venta->numero_pedido= $data['numero_pedido'];

        $objAccesoDatos = ManipularDatos::obtenerInstancia();

        try{
            $resultado = $venta->modificarVenta($objAccesoDatos);
            $response->getBody()->write(json_encode(['mensaje' => $resultado]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

    }

    public function exportarArchivosCSV(Request $request, Response $response, $args) {
        try {
            $productos = new Venta();
            $csv = $productos->exportarArchivoCSV();

            $response->getBody()->write($csv);
            return $response
                ->withHeader('Content-Type', 'text/csv')
                ->withHeader('Content-Disposition', 'attachment; filename="exportacion.csv"')
                ->withStatus(200);
        } catch (Exception $e) {
            $response->getBody()->write("Error: " . $e->getMessage());
            return $response->withHeader('Content-Type', 'text/plain')->withStatus(500);
        }
    }
}