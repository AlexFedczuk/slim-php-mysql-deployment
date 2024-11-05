<?php
require_once './models/Producto.php';

class ProductoController
{
    // Crear un nuevo producto
    public function CrearProducto($request, $response, $args)
    {
        $params = $request->getParsedBody();

        // Verificar que los parámetros requeridos estén presentes y no sean nulos
        if (empty($params['nombre']) || empty($params['tipo']) || !isset($params['precio'])) {
            $payload = json_encode(["mensaje" => "ERROR: Faltan datos necesarios (nombre, tipo o precio)"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Verificar que el tipo de producto sea válido
        if (!in_array(strtolower($params['tipo']), ['bebida', 'comida'])) {
            $payload = json_encode(array("mensaje" => "ERROR: El tipo de producto no es valido."));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Verificar que el precio del producto sea válido
        if($params['precio'] < 0){
            $payload = json_encode(array("mensaje" => "ERROR: El precio del producto no es valido."));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Crear el producto
        $producto = new Producto();
        $producto->nombre = ucwords(strtolower($params['nombre']));
        $producto->tipo = strtolower($params['tipo']);
        $producto->precio = $params['precio'];
        $producto->descripcion = $params['descripcion'] ?? '';

        $producto->crearProducto();

        $payload = json_encode(array("mensaje" => "SUCCESS: Producto creado con exito!"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Listar todos los productos
    public function ListarProductos($request, $response, $args)
    {
        $productos = Producto::obtenerTodos();
        $payload = json_encode(array("productos" => $productos));
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}