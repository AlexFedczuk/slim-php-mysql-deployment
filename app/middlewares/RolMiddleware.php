<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class VerificarRolMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // Aquí puedes obtener el rol del usuario desde el token o sesión
        // Este ejemplo asume que el rol se pasa como un parámetro de consulta
        $params = $request->getQueryParams();
        $rol = $params['rol'] ?? null; // Aquí deberías obtener el rol del token o sesión

        // Verifica si el rol es válido
        if ($rol === 'socio' || $rol === 'administrador') {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'SUCCES: Acceso otorgado.'));
            $response->getBody()->write($payload);
            return $handler->handle($request); // Permitir el acceso
        } else {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'Error: No tienes permisos para acceder a esta ruta, rol inválido.'));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }
    }
}