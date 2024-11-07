<?php
require_once './models/Mesa.php';
require_once './models/Empleado.php';

class MesaController
{
    // Crear una nueva mesa
    public function CrearMesa($request, $response, $args)
    {
        $params = $request->getParsedBody();

        // Verificar que los parámetros requeridos estén presentes y no sean nulos
        if (!isset($params['mozo_responsable'])) {
            $payload = json_encode(["mensaje" => "ERROR: Faltan datos necesarios (id del mozo responsable)"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validar el mozo responsable
        $error_mozo = Empleado::validarMozoResponsable($params['mozo_responsable']);
        if ($error_mozo) {
            $payload = json_encode(array("mensaje" => $error_mozo));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Generar el ID de la mesa
        $mesa_id = self::generarIdMesa();
        
        // Crear la mesa
        $mesa = new Mesa();
        $mesa->id = $mesa_id;
        $mesa->estado = 'con cliente esperando pedido';  // Estado inicial
        $mesa->mozo_responsable = $params['mozo_responsable'];
        
        $mesa->crearMesa();

        $payload = json_encode(array("mensaje" => "SUCCESS: Mesa creada con exito!", "mesa_id" => $mesa_id));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Listar todas las mesas
    public function ListarMesas($request, $response, $args)
    {
        $mesas = Mesa::obtenerTodas();

        if(!$mesas) {
            $payload = json_encode(["mensaje" => "ERROR: No hay mesas para listar."]);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404)->write($payload);
        }

        $payload = json_encode(array("mesas" => $mesas));   
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Listar UN empleado
    public function ListarUnaMesa($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $id = $params['id'] ?? null;

        if (is_null($id)) {
            $payload = json_encode(["mensaje" => "ERROR: Faltan datos necesarios (id)"]);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400)->write($payload);
        }

        $mesa = Mesa::obtenerPorId($id);

        if (!$mesa) {
            $payload = json_encode(["mensaje" => "ERROR: No hay una mesa con el ID: " . $id . "."]);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404)->write($payload);
        }

        $payload = json_encode($mesa);
        return $response->withHeader('Content-Type', 'application/json')->write($payload);
    }

    public function BorrarMesa($request, $response, $args)
    {
        $id = $args['id'] ?? null;

        if (is_null($id)) {
            $payload = json_encode(["mensaje" => "ERROR: Faltan datos necesarios (id)"]);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400)->write($payload);
        }

        $mesa = Mesa::obtenerPorId($id);
        if (!$mesa) {
            $payload = json_encode(["mensaje" => "ERROR: No hay una Mesa con el ID: " . $id . "."]);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404)->write($payload);
        }

        Mesa::borrarMesa($id);

        $payload = json_encode(["mensaje" => "SUCCESS: Mesa con ID: " . $id . " ha sido eliminada."]);
        return $response->withHeader('Content-Type', 'application/json')->write($payload);
    }

    // Cambiar el estado de una mesa
    public function CambiarEstadoMesa($request, $response, $args)
    {
        $params = $request->getParsedBody();
        
        // Verificar si la mesa existe
        $mesa = Mesa::obtenerPorId($args['id']);
        if (!$mesa) {
            $payload = json_encode(array("mensaje" => "ERROR: El ID ingresado no coincide con ninguna mesa."));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validar el estado
        $estados_permitidos = self::obtenerEstadosPermitidos();
        $estado = strtolower($params['estado']);
        if (!in_array($estado, $estados_permitidos)) {
            $payload = json_encode(array("mensaje" => "ERROR: El estado ingresado no es valido."));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Cambiar el estado de la mesa
        Mesa::cambiarEstado($args['id'], $estado);

        $payload = json_encode(array("mensaje" => "SUCCESS: Estado de la mesa actualizado con exito!"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Método para obtener los estados permitidos para las mesas
    private static function obtenerEstadosPermitidos()
    {
        static $estados_permitidos = null;

        if ($estados_permitidos === null) {
            $json_data = file_get_contents('./data/estados_de_mesas.json');
            $estados_data = json_decode($json_data, true);
            $estados_permitidos = array_map('strtolower', $estados_data['estados']);
        }

        return $estados_permitidos;
    }

    // Método para generar automáticamente un ID alfanumérico de 5 caracteres
    private static function generarIdMesa()
    {
        return substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 5);
    }

    public function ObtenerInformeDeUsoDeMesas($request, $response, $args)
    {
        // Obtener el informe de uso de mesas
        $informe = Mesa::contarPedidosPorMesa();

        $payload = json_encode(array("informe_mesas" => $informe));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}