<?php
//php -S localhost:666 -t app
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Dotenv\Dotenv;
require __DIR__ . '/../vendor/autoload.php';
require_once '../app/db/ManipularDatos.php';
require_once '../app/controller/ProductosController.php';
require_once '../app/controller/VentaController.php';
require_once '../app/controller/UsuarioController.php';
require_once '../app/middleware/MiddlewareAutenticacion.php';
require_once '../app/middleware/MiddlewareLogIn.php';
require_once '../app/utils/AutentificadorJWT.php';
require_once '../app/middleware/ConfirmarPerfil.php';


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$app = AppFactory::create();
$conexion = ManipularDatos::obtenerInstancia()->obtenerConexion();

$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();

$app->get('[/]', function (Request $request, Response $response) {    
    $payload = json_encode(array("mensaje" => "Conexion exitosa!"));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
    });

$app->group('/tienda', function(RouteCollectorProxy $group){
    $group->post('/alta', function(Request $request, Response $response, $args) {
        $controlador = new ProductosController();
        return $controlador->altaProducto($request, $response, $args);
    });
    $group->post('/consultar', function(Request $request, Response $response, $args){
        $controlador = new ProductosController();
        return $controlador->consultarProducto($request, $response, $args);
    });
    $group->put('/modificar', function(Request $request, Response $response, $args){
        $controlador = new VentaController();
        return $controlador->modificarVenta($request, $response, $args);
    });
})->add(new ConfirmarPerfil(['admin']));

$app->group('/venta', function(RouteCollectorProxy $group){
    $group->post('/alta', function(Request $request, Response $response, $args){
        $controlador = new VentaController();
        return $controlador->altaNuevaVenta($request, $response, $args);
    });
    $group->group('/consultar', function(RouteCollectorProxy $group){
        $group->get('/productos/vendidos', function(Request $request, Response $response, $args){
            $controlador = new ProductosController();
            return $controlador->productosVendidos($request, $response, $args);
        });
        $group->get('/ventas/porUsuario',function(Request $request, Response $response, $args){
            $controlador = new VentaController();
            return $controlador->traerVentasPorUsuario($request, $response, $args);
        });
        $group->get('/productos/porProducto', function(Request $request, Response $response, $args){
            $controlador = new VentaController();
            return $controlador->traerVentaPorProducto($request, $response, $args);
        });
        $group->get('/productos/entreValores', function(Request $request, Response $response, $args){
            $controlador = new ProductosController();
            return $controlador->productosEntreDosPrecios($request, $response, $args);
        });
        $group->get('/ventas/ingresos', function(Request $request, Response $response, $args){
            $controlador = new VentaController();
            return $controlador->traerGananciasPorVentas($request, $response, $args);
        });
        $group->get('/productos/masVendido', function(Request $request, Response $response, $args){
            $controlador = new VentaController();
            return $controlador->obtenerProductoMasVendido($request, $response, $args);
        });
    });
})->add(new ConfirmarPerfil(['admin', 'empleado']));

$app->post('/registro', function(Request $request, Response $response, $args){
    $controlador = new UsuarioController();
    return $controlador->crearUsuario($request, $response, $args);
});

$app->group('/jwt', function (RouteCollectorProxy $group){
    $group->post('/crearToken', function(Request $request, Response $response, $args){
        $parametros = $request->getParsedBody();
        $usuario = $parametros['usuario'];
        $perfil =$parametros['perfil'];
        $datos = array('usuario' => $usuario, 'perfil' => $perfil);
        $token = AutentificadorJWT::CrearToken($datos);
        $payload = json_encode(array('jwt' => $token));
        $response->getBody()->write($payload);
        return $response
        ->withHeader('Content-Type', 'application/json');
    });
    $group->get('/devolverPayLoad', function (Request $request, Response $response) {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
    
            try {
            $payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayLoad($token)));
        } catch (Exception $e) {
            $payload = json_encode(array('error' => $e->getMessage()));
        }
    
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
        });
        $group->get('/devolverDatos', function (Request $request, Response $response) {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
    
        try {
            $payload = json_encode(array('datos' => AutentificadorJWT::ObtenerData($token)));
        } catch (Exception $e) {
            $payload = json_encode(array('error' => $e->getMessage()));
        }
    
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
        });
    
        $group->get('/verificarToken', function (Request $request, Response $response) {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $esValido = false;
        try {
            AutentificadorJWT::verificarToken($token);
            $esValido = true;
        } catch (Exception $e) {
            $payload = json_encode(array('error' => $e->getMessage()));
        }
    
        if ($esValido) {
            $payload = json_encode(array('valid' => $esValido));
        }
    
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
        });
    });

$app->get('/ventas/descargar', function(Request $request, Response $response, $args){
    $controlador = new VentaController();
    return $controlador->exportarArchivosCSV($request, $response, $args);
})->add(new ConfirmarPerfil(['admin']));


$app->post('/login', function (Request $request, Response $response) {    
    $parametros = $request->getParsedBody();
    $usuario = $parametros['usuario'];
    $password = $parametros['password'];

    $usuario = new Usuario();
    $datos = array('usuario' => $usuario);
    $token = AutentificadorJWT::CrearToken($datos);
    $payload = json_encode(array('jwt' => $token));
    $response->getBody()->write($payload);
    return $response
    ->withHeader('Content-Type', 'application/json');
})->add(new MiddlewareAutenticacion());



$app->run();
