<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

class RolMiddleware
{
    private $rolesPermitidos;

    public function __construct(array $rolesPermitidos)
    {
        $this->rolesPermitidos = $rolesPermitidos;
    }

    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        $usuario = $request->getAttribute('usuario');

        // Verificar que el usuario tenga un rol
        if (!isset($usuario->rol)) {
            $response = new Response();
            $response->getBody()->write(json_encode(["mensaje" => "Error: Rol no proporcionado."]));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }

        // Verificar si el rol del usuario está en los roles permitidos
        if (!in_array($usuario->rol, $this->rolesPermitidos)) {
            $response = new Response();
            $response->getBody()->write(json_encode(["mensaje" => "Error: Acceso denegado."]));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }

        // Continuar con el siguiente middleware o controlador
        return $handler->handle($request);
    }
}