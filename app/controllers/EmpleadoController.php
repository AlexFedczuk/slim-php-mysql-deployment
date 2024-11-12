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
        if (empty($params['nombre']) || empty($params['rol'])) {
            $payload = json_encode(["mensaje" => "ERROR: Faltan datos necesarios (nombre o rol)"]);
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

        if (!$empleados) {
            $payload = json_encode(["mensaje" => "ERROR: No hay empleados para listar."]);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404)->write($payload);
        }
        
        $payload = json_encode(array("empleados" => $empleados));   
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Listar UN empleado
    public function ListarUnEmpleado($request, $response, $args)
    {
        $id = $args['id'] ?? null;

        // Verificar que los parámetros requeridos estén presentes y no sean nulos
        if (empty($id)) {
            $payload = json_encode(["mensaje" => "ERROR: Faltan datos necesarios (id)"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Obtener el empleado por ID
        $empleado = Empleado::obtenerPorId($id);
        if (!$empleado) {
            $payload = json_encode(["mensaje" => "ERROR: No hay un empleado con el ID: " . $id]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $payload = json_encode($empleado);
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

    public function BorrarEmpleado($request, $response, $args)
    {
        $id = $args['id'] ?? null;  // Obtener el ID del empleado desde los parámetros de la URL

        // Verificar que los parámetros requeridos estén presentes y no sean nulos
        if (empty($id)) {
            $payload = json_encode(["mensaje" => "ERROR: Faltan datos necesarios (id)"]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Verificar que el empleado existe
        $empleado = Empleado::obtenerPorId($id);
        if (!$empleado) {
            $payload = json_encode(array("mensaje" => "ERROR: No hay un empleado con el ID: $id."));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Lógica para eliminar el empleado
        Empleado::borrarEmpleado($id);

        $payload = json_encode(array("mensaje" => "SUCCESS: Empleado con ID $id ha sido eliminado."));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function CargarEmpleadosDesdeCSV($request, $response, $args)
    {
        // Verificar que el archivo se haya subido
        if (!$request->getUploadedFiles()) {
            $payload = json_encode(['mensaje' => 'ERROR: No se ha enviado ningún archivo.']);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $uploadedFiles = $request->getUploadedFiles();
        $csvFile = $uploadedFiles['csv_file'] ?? null;

        if ($csvFile && $csvFile->getError() === UPLOAD_ERR_OK) {
            // Leer el contenido del archivo CSV
            $csvContent = file_get_contents($csvFile->getStream()->getMetadata('uri'));
            $lines = explode("\n", $csvContent); // Dividir en líneas

            $header = str_getcsv(array_shift($lines)); // Obtener la primera línea (encabezado)

            // Variables para contar la cantidad de empleados cargados y errores
            $empleadosCargados = 0;
            $errores = [];

            // Recorrer las líneas del CSV y agregar a la base de datos
            foreach ($lines as $line) {
                if (empty($line)) continue; // Ignorar líneas vacías

                $data = str_getcsv($line);
                $empleado = [
                    'nombre' => $data[0] ?? null,
                    'rol' => $data[1] ?? null,
                    'estado' => $data[2] ?? null
                ];

                // Validar que los datos sean correctos
                if (Empleado::validarEmpleado($empleado)) {
                    // Si es válido, guardar el empleado en la base de datos
                    Empleado::crearEmpleado($empleado);
                    $empleadosCargados++;
                } else {
                    $errores[] = "Empleado con datos inválidos: " . implode(",", $empleado);
                }
            }

            // Responder con los resultados
            $mensaje = [
                'empleados_cargados' => $empleadosCargados,
                'errores' => $errores
            ];
            $payload = json_encode($mensaje);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $payload = json_encode(['mensaje' => 'Error al cargar el archivo CSV.']);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    public function DescargarEmpleadosCSV($request, $response, $args)
    {
        $empleados = Empleado::obtenerTodos();

        if (!$empleados) {
            $payload = json_encode(["mensaje" => "ERROR: No hay empleados para descargar."]);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $filename = 'empleados_' . date('Y-m-d_H-i-s') . '.csv';
        $file = fopen('php://temp', 'w');

        // Encabezado del archivo CSV
        $header = ['ID', 'Nombre', 'Rol', 'Estado'];
        fputcsv($file, $header);

        // Recorrer los empleados y escribe cada uno en el CSV
        foreach ($empleados as $empleado) {
            fputcsv($file, [$empleado->id, $empleado->nombre, $empleado->rol, $empleado->estado]);
        }

        // Mover el puntero del archivo al inicio
        rewind($file);

        // Setear las cabeceras para la descarga del archivo CSV
        $response = $response->withHeader('Content-Type', 'text/csv')
        ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');

        // Escribir el contenido del archivo CSV en el cuerpo de la respuesta
        $response->getBody()->write(stream_get_contents($file));

        fclose($file);

        return $response;
    }

}