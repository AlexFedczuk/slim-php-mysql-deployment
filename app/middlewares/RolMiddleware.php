<?php
class RolMiddleware
{
    private $rol_requerido;
    
    public function __construct($rol_requerido)
    {
        $this->rol_requerido = $rol_requerido;
    }

    public function __invoke($request, $handler)
    {
        // Obtener el usuario y su rol (en un entorno real, el usuario debe estar autenticado)
        $usuario = $request->getAttribute('usuario');  // Se asume que el usuario y rol están en la solicitud

        if ($usuario && $usuario->rol === $this->rol_requerido) {
            return $handler->handle($request);
        }

        // Si el rol no coincide, denegar acceso
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode(array("mensaje" => "ERROR: No tienes permiso para acceder a este recurso.")));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }
}
