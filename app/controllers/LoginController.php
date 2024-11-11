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

        $result = LoginController::verificarCredenciales($username, $password);

        if ($result) {
            // Si las credenciales son válidas, genera el token
            $now = time();
            $payload = [
                "iat" => $now,
                "exp" => $now + (60 * 60), // El token expira en 1 hora
                "data" => [
                    "username" => $username,
                    "rol" => $result['rol']
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

    public static function verificarCredenciales($usuario, $password)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        
        $consulta = $objAccesoDatos->prepararConsulta("SELECT usuario, contraseña, rol FROM usuarios WHERE usuario = :usuario");
        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();

        // Resultado de la consulta
        $usuarioBD = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($usuarioBD) {
            // Verificar la contraseña
            if ($password == $usuarioBD['contraseña']) {
                // Si correcta, devolver los datos del usuario
                return [
                    'usuario' => $usuarioBD['usuario'],
                    'rol' => $usuarioBD['rol']
                ];
            } else {
                // Contraseña incorrecta
                return false;
            }
        } else {
            // El usuario no existe
            return false;
        }
    }
}
