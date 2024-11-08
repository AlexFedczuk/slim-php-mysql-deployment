<?php

use Firebase\JWT\JWT;
use Slim\Psr7\Response;

class LoginController
{
    private $secretKey;

    public function __construct($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function login($request, $response, $args)
    {
        $params = (array)$request->getParsedBody();
        $username = $params['username'] ?? '';
        $password = $params['password'] ?? '';

        // Verificar las credenciales en la base de datos.
        // Ejemplo básico sin base de datos.
        if ($username === 'admin' && $password === '12345') {
            // Si las credenciales son válidas, genera el token
            $now = time();
            $payload = [
                "iat" => $now,
                "exp" => $now + (60 * 60), // El token expira en 1 hora
                "data" => [
                    "username" => $username,
                    "rol" => "administrador" // Aquí defines el rol, en este caso 'administrador'
                ]
            ];

            $jwt = JWT::encode($payload, $this->secretKey, 'HS256');

            $response->getBody()->write(json_encode(["token" => $jwt]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            // Si las credenciales no son válidas, retorna un error
            $response->getBody()->write(json_encode(["mensaje" => "ERROR: Credenciales inválidas."]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }
    }
}
