<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

class RolMiddleware
{
    protected $rolesPermitidos; // Roles permitidos para la ruta
    protected $claveSecreta; // Roles permitidos para la ruta

    // Recibe los roles permitidos como parámetro
    public function __construct(array $rolesPermitidos, string $claveSecreta)
    {
        $this->rolesPermitidos = $rolesPermitidos;
        $this->claveSecreta = $claveSecreta;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $token = $request->getHeaderLine('Authorization'); // Obtener el token

        if (empty($token)) {
            return $this->forbiddenResponse("ERROR: Token no proporcionado", 401);
        }

        // Elimina el prefijo "Bearer " del token
        $token = str_replace("Bearer ", "", $token);

        try {
            // Decodificar el token con la clave secreta
            //$decoded = JWT::decode($token, $this->claveSecreta, ['HS256']);
            $decoded = JWT::decode($token, new Key($this->claveSecreta, 'HS256'));

            // Verificar que el rol exista en el token decodificado
            if (!isset($decoded->rol)) {
                throw new Exception("El rol no está presente en el token.");
            }

            // Extraemos el rol desde el token decodificado
            $role = $decoded->rol; // Aquí es donde tienes el rol del usuario, ajusta si es diferente en tu token

            // Verificamos si el rol del usuario está en los roles permitidos
            if (!in_array($role, $this->rolesPermitidos)) {
                return $this->forbiddenResponse("ADVERTENCIA: No tienes permisos para acceder a esta ruta", 403);
            }

            // Continuar con la siguiente acción (controlador)
            return $handler->handle($request);
        } catch (Exception $e) {
            return $this->forbiddenResponse("ERROR: Token inválido o expirado", 401);
        }
    }

    private function forbiddenResponse(string $message, int $statusCode): Response
    {
        $response = new Response();
        $payload = json_encode(['mensaje' => $message]);
        $response->getBody()->write($payload);
        return $response->withStatus($statusCode)->withHeader('Content-Type', 'application/json');
    }
}