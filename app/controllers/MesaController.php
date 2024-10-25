<?php
require_once './models/Mesa.php';
require_once './models/Empleado.php';

class MesaController
{
    // Crear una nueva mesa
    public function CrearMesa($request, $response, $args)
    {
        $params = $request->getParsedBody();

        // Validar el mozo responsable
        $error_mozo = self::validarMozoResponsable($params['mozo_responsable']);
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
        $lista = Mesa::obtenerTodas();
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
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

    // Método para validar si el mozo existe y tiene el rol adecuado
    private static function validarMozoResponsable($mozo_responsable_id)
    {
        $mozo_responsable = Empleado::obtenerPorId($mozo_responsable_id);
        if (!$mozo_responsable) {
            return "ERROR: El mozo responsable no existe.";
        }
        if ($mozo_responsable->rol !== 'mozo') {
            return "ERROR: El empleado relacionado no tiene el rol de mozo.";
        }
        if ($mozo_responsable->estado == 'suspendido') {
            return "ERROR: El empleado relacionado está suspendido.";
        }
        return null;  // Sin errores
    }

    // Método para generar automáticamente un ID alfanumérico de 5 caracteres
    private static function generarIdMesa()
    {
        return substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 5);
    }
}