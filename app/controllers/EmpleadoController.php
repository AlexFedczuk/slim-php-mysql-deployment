<?php
require_once './models/Empleado.php';

class EmpleadoController
{
    // Crear un nuevo empleado
    public function CrearEmpleado($request, $response, $args)
    {
        $params = $request->getParsedBody();

        // Formatear el nombre
        $nombre = self::formatearNombre($params['nombre']);

        // Obtener los roles permitidos (cargados solo una vez)
        $roles = self::obtenerRolesPermitidos();

        // Validar el rol
        $rol = strtolower($params['rol']);
        if (!in_array($rol, $roles)) {
            $payload = json_encode(array("mensaje" => "ERROR: El rol ingresado no es valido."));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Crear el empleado si las validaciones pasan
        $empleado = new Empleado();
        $empleado->nombre = $nombre;
        $empleado->rol = $rol;
        $empleado->estado = 'activo'; // Por defecto, estado 'activo'

        $empleado->crearEmpleado();

        $payload = json_encode(array("mensaje" => "SUCCESS: Empleado creado con exito!"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Listar todos los empleados
    public function ListarEmpleados($request, $response, $args)
    {
        $lista = Empleado::obtenerTodos();
        $payload = json_encode(array("listaEmpleados" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Cambiar el estado de un empleado
    public function CambiarEstadoEmpleado($request, $response, $args)
    {
        $params = $request->getParsedBody();

        // Verificar que el empleado exista
        $empleado = Empleado::obtenerPorId($args['id']);
        if (!$empleado) {
            $payload = json_encode(array("mensaje" => "ERROR: El ID ingresado no coincide con ningun empleado."));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validar el estado
        $estado = strtolower($params['estado']);
        if (!self::validarEstado($estado)) {
            $payload = json_encode(array("mensaje" => "ERROR: El estado ingresado no es valido."));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Cambiar el estado del empleado
        Empleado::cambiarEstado($args['id'], $estado);

        $payload = json_encode(array("mensaje" => "SUCCESS: Estado del empleado actualizado con exito!"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Método para cargar los roles solo una vez
    private static function obtenerRolesPermitidos()
    {
        static $roles_permitidos = null;
        
        if ($roles_permitidos === null) {
            $json_data = file_get_contents('./data/roles.json');
            $roles_data = json_decode($json_data, true);
            $roles_permitidos = array_map('strtolower', $roles_data['roles']);
        }

        return $roles_permitidos;
    }

    // Método para cargar los estados permitidos desde JSON solo una vez
    private static function obtenerEstadosPermitidos()
    {
        static $estados_permitidos = null;
        
        if ($estados_permitidos === null) {
            $json_data = file_get_contents('./data/estados_de_empleados.json');
            $estados_data = json_decode($json_data, true);
            $estados_permitidos = array_map('strtolower', $estados_data['estados']);
        }

        return $estados_permitidos;
    }

    // Método para formatear el nombre
    private static function formatearNombre($nombre)
    {
        return ucwords(strtolower($nombre));
    }

    // Método para validar si el estado es permitido
    private static function validarEstado($estado)
    {
        $estados_permitidos = self::obtenerEstadosPermitidos();
        return in_array(strtolower($estado), $estados_permitidos);
    }
}