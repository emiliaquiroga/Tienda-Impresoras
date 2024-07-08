<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class ConfirmarPerfil
{
    private $perfilesValidos;

    public function __construct(array $perfilesValidos)
    {
        $this->perfilesValidos = $perfilesValidos;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        try {
            AutentificadorJWT::VerificarToken($token);
            $data = AutentificadorJWT::ObtenerData($token);
            $perfil = $data->perfil ?? null;

            if (!in_array($perfil, $this->perfilesValidos)) {
                throw new Exception('Perfil no autorizado.');
            }

            $response = $handler->handle($request);
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(['mensaje' => $e->getMessage()]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        return $response;
    }
}
