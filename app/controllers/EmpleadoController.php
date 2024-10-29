<?php
require_once './models/Empleado.php';
require_once './interfaces/IApiUsable.php';

class EmpleadoController
{
    // Crear un nuevo empleado
    public function CrearEmpleado($request, $response, $args)
    {
        $params = $request->getParsedBody();

        // Verificar que los parámetros requeridos estén presentes y no sean nulos
        if (empty($params['nombre']) || empty($params['clave']) || empty($params['rol'])) {
            $payload = json_encode(["mensaje" => "ERROR: Faltan datos necesarios (nombre, clave o rol)"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validar el rol
        if (!Empleado::esRolValido($params['rol'])) {
            $payload = json_encode(["mensaje" => "ERROR: El rol ingresado no es válido"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Crear el empleado
        $empleado = new Empleado();
        $empleado->nombre = ucwords(strtolower($params['nombre']));
        $empleado->clave = $params['clave'];
        $empleado->rol = strtolower($params['rol']);
        
        $empleado->guardar(); // Mueve la lógica de guardado al modelo

        $payload = json_encode(["mensaje" => "SUCCESS: Empleado creado con éxito"]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Listar todos los empleados
    public function ListarEmpleados($request, $response, $args)
    {
        $empleados = Empleado::obtenerTodos();
        $payload = json_encode(["empleados" => $empleados]);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Cambiar el estado de un empleado
    public function CambiarEstadoEmpleado($request, $response, $args)
    {
        $id = $args['id'];
        $params = $request->getParsedBody();

        // Verificar que el estado esté presente y no sea nulo
        if (empty($params['estado'])) {
            $payload = json_encode(["mensaje" => "ERROR: Faltan datos necesarios (estado)"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Verificar si el empleado existe
        $empleado = Empleado::obtenerPorId($id);
        if (!$empleado) {
            $payload = json_encode(["mensaje" => "ERROR: El ID ingresado no coincide con ningún empleado"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Validar el estado
        if (!Empleado::esEstadoValido($params['estado'])) {
            $payload = json_encode(["mensaje" => "ERROR: El estado ingresado no es válido"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Cambiar el estado
        $empleado->cambiarEstado($params['estado']);

        $payload = json_encode(["mensaje" => "SUCCESS: Estado del empleado actualizado con éxito"]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}