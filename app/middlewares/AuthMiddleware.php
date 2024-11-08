<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthMiddleware
{
    private $secretKey;

    public function __construct($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Obtener el token de los headers
        $token = $request->getHeaderLine('Authorization');

        // Comprobar si el token está presente
        if (empty($token)) {
            $response = new Response();
            $response->getBody()->write(json_encode(["mensaje" => "ERROR: Token no proporcionado."]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // Eliminar el prefijo "Bearer " del token
        $token = str_replace('Bearer ', '', $token);

        try {
            // Decodificar el token
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            //$decoded = JWT::decode($token, $this->secretKey, ['HS256']);
            //$decoded = JWT::decode($token, $this->secretKey, 'HS256');
            //$decoded = JWT::decode($token, $this->secretKey, array('HS256'));

            // Verificar que el resultado sea un objeto válido
            if (!is_object($decoded)) {
                throw new Exception("Token inválido");
            }

            // Almacenar los datos del usuario en el request
            $request = $request->withAttribute('usuario', $decoded);
        } catch (Exception $e) {
            $response = new Response();
            $response->getBody()->write(json_encode(["mensaje" => "ERROR: Token inválido: " . $e->getMessage()]));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // Continuar con el siguiente middleware o controlador
        return $handler->handle($request);
    }
}