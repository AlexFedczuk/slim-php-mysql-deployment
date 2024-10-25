<?php
require_once './models/Pedido.php';
require_once './models/Empleado.php';

class PedidoController
{
    // Crear un nuevo pedido
    public function CrearPedido($request, $response, $args)
    {
        $params = $request->getParsedBody();

        // Validar el mozo responsable
        $error_mozo = Empleado::validarMozoResponsable($params['mozo_responsable']);
        if ($error_mozo) {
            $payload = json_encode(array("mensaje" => $error_mozo));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Verificar que la mesa exista
        $mesa = Mesa::obtenerPorId($params['mesa_id']);
        if (!$mesa) {
            $payload = json_encode(array("mensaje" => "ERROR: La mesa no existe."));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Crear el pedido
        $pedido = new Pedido();
        $pedido->mesa_id = $params['mesa_id'];
        $pedido->cliente_nombre = ucfirst(strtolower($params['cliente_nombre'])); // Formatear nombre del cliente
        $pedido->productos = json_encode($params['productos']);  // Convertir productos a JSON
        $pedido->mozo_responsable = $params['mozo_responsable'];
        $pedido->estado = 'pendiente';  // Estado inicial

        // Guardar el pedido en la base de datos
        $pedido->crearPedido();

        $payload = json_encode(array("mensaje" => "SUCCESS: Pedido creado con exito!"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}