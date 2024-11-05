<?php
require_once './models/Pedido.php';
require_once './models/Empleado.php';

class PedidoController
{
    // Crear un nuevo pedido
    public function CrearPedido($request, $response, $args)
    {
        $params = $request->getParsedBody();

        // Verificar que los parámetros requeridos estén presentes y no sean nulos
        if (empty($params['mesa_id']) || empty($params['cliente_nombre']) || empty($params['productos']) || empty($params['mozo_responsable'])) {
            $payload = json_encode(["mensaje" => "ERROR: Faltan datos necesarios (id de la mesa, nombre del cliente, productos o id del mozo responsable)"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Verificar que la mesa exista
        if (!$this->verificarMesaExiste($params['mesa_id'], $response)) {
            return $response;
        }

        // Validar productos
        $productos = json_decode($params['productos'], true);
        foreach ($productos as $productoNombre) {
            if (!Producto::obtenerPorNombre($productoNombre)) {
                $payload = json_encode(array("mensaje" => "ERROR: El producto '$productoNombre' no esta registrado en la DB."));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        }

        // Validar el mozo responsable
        $error_mozo = Empleado::validarMozoResponsable($params['mozo_responsable']);
        if ($error_mozo) {
            return $this->responderConJson($response, ["mensaje" => $error_mozo], 400);
        }

        // Crear el pedido
        $pedido = new Pedido();
        $pedido->mesa_id = $params['mesa_id'];
        $pedido->cliente_nombre = ucwords(strtolower($params['cliente_nombre'])); // Formatear nombre del cliente
        $pedido->productos = is_array($params['productos']) ? json_encode($params['productos']) : $params['productos'];
        $pedido->mozo_responsable = $params['mozo_responsable'];
        $pedido->estado = 'pendiente';  // Estado inicial
        $pedido->crearPedido();

        return $this->responderConJson($response, ["mensaje" => "SUCCESS: Pedido creado con exito!"]);
    }

    // Obtener un pedido específico por ID
    public function ObtenerPedido($request, $response, $args)
    {
        $pedido_id = $args['id'];
        $pedido = Pedido::obtenerPorId($pedido_id);
        
        if ($pedido) {
            return $this->responderConJson($response, ["pedido" => $pedido]);
        } else {
            return $this->responderConJson($response, ["mensaje" => "ERROR: El pedido no existe."], 404);
        }
    }

    // Cambiar el estado de un pedido
    public function CambiarEstadoPedido($request, $response, $args)
    {
        $pedido_id = $args['id'];
        $params = $request->getParsedBody();

        // Verificar que los parámetros requeridos estén presentes y no sean nulos
        if (empty($params['estado'])) {
            $payload = json_encode(["mensaje" => "ERROR: Faltan datos necesarios (estado)"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $nuevo_estado = strtolower($params['estado']);

        // Verificar si el pedido existe
        if (!$this->verificarPedidoExiste($pedido_id, $response)) {
            return $response;
        }

        // Validar el nuevo estado
        if (!in_array($nuevo_estado, self::obtenerEstadosPermitidos())) {
            return $this->responderConJson($response, ["mensaje" => "ERROR: El estado ingresado no es valido."], 400);
        }

        try {
            Pedido::cambiarEstado($pedido_id, $nuevo_estado);
            $payload = json_encode(array("mensaje" => "SUCCESS: Estado del pedido actualizado con exito!"));
        } catch (Exception $e) {
            $payload = json_encode(array("mensaje" => $e->getMessage()));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400)->write($payload);
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Listar pedidos por estado
    public function ListarPedidosPorEstado($request, $response, $args)
    {
        $estado = strtolower($request->getQueryParams()['estado'] ?? '');

        if (!in_array($estado, self::obtenerEstadosPermitidos()) && $estado !== "") {
            return $this->responderConJson($response, ["mensaje" => "ERROR: El estado ingresado no es valido."], 400);
        }

        $pedidos = Pedido::obtenerPorEstado($estado);
        $payload = $pedidos ? ["pedidos" => $pedidos] : ["mensaje" => "No hay pedidos con el estado especificado."];
        return $this->responderConJson($response, $payload);
    }

    // Listar todos los pedidos
    public function ListarTodosLosPedidos($request, $response, $args)
    {
        $pedidos = Pedido::obtenerTodos();
        $payload = $pedidos ? ["pedidos" => $pedidos] : ["mensaje" => "No hay pedidos registrados."];
        return $this->responderConJson($response, $payload);
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

    // Método genérico para responder en JSON
    private function responderConJson($response, $data, $status = 200)
    {
        $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    // Verificar si la mesa existe
    private function verificarMesaExiste($mesa_id, $response)
    {
        $mesa = Mesa::obtenerPorId($mesa_id);
        if (!$mesa) {
            $this->responderConJson($response, ["mensaje" => "ERROR: La mesa no existe."], 400);
            return false;
        }
        return true;
    }

    // Verificar si el pedido existe
    private function verificarPedidoExiste($pedido_id, $response)
    {
        $pedido = Pedido::obtenerPorId($pedido_id);
        if (!$pedido) {
            $this->responderConJson($response, ["mensaje" => "ERROR: El pedido no existe."], 404);
            return false;
        }
        return true;
    }
}