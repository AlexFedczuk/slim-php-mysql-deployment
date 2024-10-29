<?php
require_once './models/Pedido.php';
require_once './models/Empleado.php';

class PedidoController
{
    // Crear un nuevo pedido
    public function CrearPedido($request, $response, $args)
    {
        $params = $request->getParsedBody();

        // Verificar que la mesa exista
        $mesa = Mesa::obtenerPorId($params['mesa_id']);
        if (!$mesa) {
            $payload = json_encode(array("mensaje" => "ERROR: La mesa no existe."));
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

        // Crear el pedido
        $pedido = new Pedido();
        $pedido->mesa_id = $params['mesa_id'];
        $pedido->cliente_nombre =  ucwords(strtolower($params['cliente_nombre'])); // Formatear nombre del cliente
        $pedido->productos = is_array($params['productos']) ? json_encode($params['productos']) : $params['productos'];
        $pedido->mozo_responsable = $params['mozo_responsable'];
        $pedido->estado = 'pendiente';  // Estado inicial

        // Guardar el pedido en la base de datos
        $pedido->crearPedido();

        $payload = json_encode(array("mensaje" => "SUCCESS: Pedido creado con exito!"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Obtener un pedido específico por ID
    public function ObtenerPedido($request, $response, $args)
    {
        $pedido_id = $args['id'];  // ID del pedido

        // Buscar el pedido en la base de datos
        $pedido = Pedido::obtenerPorId($pedido_id);
        
        if ($pedido) {
            $payload = json_encode(array("pedido" => $pedido));
        } else {
            // Si el pedido no existe, devolver un mensaje de error
            $payload = json_encode(array("mensaje" => "ERROR: El pedido no existe."));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CambiarEstadoPedido($request, $response, $args)
    {
        $pedido_id = $args['id'];  // ID del pedido
        $params = $request->getParsedBody();  // Obtener el nuevo estado desde el cuerpo de la solicitud
        $nuevo_estado = strtolower($params['estado']);  // Convertir el estado a minúsculas para evitar inconsistencias

        // Verificar si el pedido existe
        $pedido = Pedido::obtenerPorId($pedido_id);
        if (!$pedido) {
            $payload = json_encode(array("mensaje" => "ERROR: El pedido no existe."));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Definir los estados permitidos
        $estados_permitidos = self::obtenerEstadosPermitidos();

        // Validar el nuevo estado
        if (!in_array($nuevo_estado, $estados_permitidos)) {
            $payload = json_encode(array("mensaje" => "ERROR: El estado ingresado no es valido."));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Cambiar el estado del pedido en la base de datos
        $pedido->estado = $nuevo_estado;
        $pedido->actualizarEstado();

        $payload = json_encode(array("mensaje" => "SUCCESS: Estado del pedido actualizado con exito!"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Método para obtener los estados permitidos para los pedidos
    private static function obtenerEstadosPermitidos()
    {
        static $estados_permitidos = null;

        if ($estados_permitidos === null) {
            $json_data = file_get_contents('./data/estados_de_pedidos.json');
            $estados_data = json_decode($json_data, true);
            $estados_permitidos = array_map('strtolower', $estados_data['estados']);
        }

        return $estados_permitidos;
    }

    public function ListarPedidosPorEstado($request, $response, $args)
    {
        $estado = strtolower($request->getQueryParams()['estado'] ?? '');  // Obtener el estado desde el parámetro de consulta

        // Definir los estados permitidos
        $estados_permitidos = self::obtenerEstadosPermitidos();
        $estados_permitidos[] = "";

        // Validar el estado
        if (!in_array($estado, $estados_permitidos)) {
            $payload = json_encode(array("mensaje" => "ERROR: El estado ingresado no es valido."));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Obtener los pedidos con el estado especificado
        $pedidos = Pedido::obtenerPorEstado($estado);
        
        if ($pedidos) {
            $payload = json_encode(array("pedidos" => $pedidos));
        } else {
            $payload = json_encode(array("mensaje" => "No hay pedidos con el estado especificado."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ListarTodosLosPedidos($request, $response, $args)
    {
        // Obtener todos los pedidos de la base de datos
        $pedidos = Pedido::obtenerTodos();
        
        if ($pedidos) {
            $payload = json_encode(array("pedidos" => $pedidos));
        } else {
            $payload = json_encode(array("mensaje" => "No hay pedidos registrados."));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}