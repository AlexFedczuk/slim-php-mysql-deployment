<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class RolMiddleware
{
    private $rolPermitido;

    public function __construct(array $rolPermitido)
    {
        $this->rolPermitido = $rolPermitido;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $params = $request->getQueryParams();
        $rol = $params['rol'] ?? null; // Cambiar esto para obtener el rol del token o sesión

        // Verificar si el rol está presente
        if (is_null($rol)) {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'ERROR: El rol es necesario para acceder a esta ruta.'));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Verificar si el rol coincide con el rol permitido
        if (in_array($rol, $this->rolPermitido)) {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'Acceso otorgado...'));
            $response->getBody()->write($payload);
            return $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'Acceso denegado: Se requiere rol de ' . $this->rolPermitido . '.'));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }
    }
}
