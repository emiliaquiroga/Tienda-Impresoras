<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
require_once '../app/models/Usuario.php';

class UsuarioController{

    public function crearUsuario(Request $request, Response $response, $args){
        $data = $request->getParsedBody();
        $uploadedFiles = $request->getUploadedFiles();

        $perfil = isset($data['perfil']) ? $data['perfil'] : '';
        if (!in_array($perfil, Usuario::$perfilesValidos)) {
            $response->getBody()->write(json_encode(['error' => 'Perfil de usuario no válido.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $usuario = new Usuario();
        $usuario->mail = $data['mail'];
        $usuario->usuario = $data['usuario'];
        $usuario->password = $data['password'];
        $usuario->perfil = $data['perfil'];
        if (isset($uploadedFiles['foto'])) {
            $imagen = $uploadedFiles['foto'];
            if ($imagen->getError() === UPLOAD_ERR_OK) {
                $filename = Usuario::GuardarImagenusuario("../ImagenesDeusuario/2024", $imagen, $usuario->usuario, $usuario->perfil, $usuario->fecha_de_alta);
                $usuario->foto = $filename;
            }
        }
        try {
            $usuario->crearUsuario();
            $response->getBody()->write(json_encode(['mensaje' => "usuario exitosa!"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}