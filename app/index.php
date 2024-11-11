<?php
/*
 * Este archivo inicializa el entorno de Slim, 
 * carga las dependencias y define las rutas de 
 * la API que se mapearán a los métodos del 
 * controlador correspondiente.
 */

// Configuración de manejo de errores
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

// Cargar dependencias
require_once './db/AccesoDatos.php';
require_once './controllers/UsuarioController.php';
require_once './controllers/EmpleadoController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './middlewares/AuthMiddleware.php';
require_once './middlewares/RolMiddleware.php';
require_once './controllers/LoginController.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();



// Roles permitidos para la ruta
$rolesPermitidos = ['socio', 'administrador'];

// Instanciar la aplicación
$app = AppFactory::create();
$app->setBasePath('/app'); // Definir base path

// Middleware de errores
$app->addErrorMiddleware(true, true, true);

// Middleware para parseo de cuerpo
$app->addBodyParsingMiddleware();

// clave secreta para el JWT
$secretKey = 'clave_secreta';
$app->post('/login', function (Request $request, Response $response, array $args) use ($secretKey) {
    $controller = new LoginController($secretKey);
    return $controller->login($request, $response, $args);
});

$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{usuario}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
});

$app->group('/empleados', function (RouteCollectorProxy $group) use ($secretKey) {
    // Ruta para crear un empleado (solo administradores)
    $group->post('/crear', \EmpleadoController::class . ':CrearEmpleado')
    ->add(new RolMiddleware(['administrador'], $secretKey));

    $group->post('/cargar_csv', \EmpleadoController::class . ':CargarEmpleadosDesdeCSV');
    //->add(new RolMiddleware(['administrador'], $secretKey));

    // Ruta para listar todos los empleados (sin restricciones)
    $group->get('/listar', \EmpleadoController::class . ':ListarEmpleados');

    // Ruta para listar un empleado por ID (sin restricciones)
    $group->get('/listar/{id}', \EmpleadoController::class . ':ListarUnEmpleado');

    // Ruta para cambiar el estado de un empleado.
    $group->post('/modificar/estado/{id}', \EmpleadoController::class . ':CambiarEstadoEmpleado');

    // Ruta para eliminar un empleado.
    $group->delete('/borrar/{id}', \EmpleadoController::class . ':BorrarEmpleado')
    ->add(new RolMiddleware(['administrador'], $secretKey)); // Middleware de autenticación para validar el token
});

// Rutas para Productos
$app->group('/productos', function (RouteCollectorProxy $group) use ($secretKey) {
  $group->post('/crear', \ProductoController::class . ':CrearProducto')
        ->add(new RolMiddleware(['administrador'], $secretKey));

  $group->get('/listar', \ProductoController::class . ':ListarProductos');

  $group->get('/listar/{id}', \ProductoController::class . ':ListarUnProducto');

  $group->delete('/borrar/{id}', \ProductoController::class . ':BorrarProducto')
        ->add(new RolMiddleware(['administrador'], $secretKey));
});

// Rutas para Mesas
$app->group('/mesas', function (RouteCollectorProxy $group) use ($secretKey) {
    $group->post('/crear', \MesaController::class . ':CrearMesa')
          ->add(new RolMiddleware(['mozo', 'administrador'], $secretKey)); // Alta de mesa

    $group->get('/listar', \MesaController::class . ':ListarMesas'); // Listar mesas

    $group->get('/informe', \MesaController::class . ':ObtenerInformeDeUsoDeMesas');

    $group->get('/listar/{id}', \MesaController::class . ':ListarUnaMesa');

    $group->post('/modificar/estado/{id}', \MesaController::class . ':CambiarEstadoMesa')
          ->add(new RolMiddleware(['socio', 'administrador'], $secretKey));

    $group->delete('/borrar/{id}', \MesaController::class . ':BorrarMesa')
          ->add(new RolMiddleware(['administrador'], $secretKey));
});

// Rutas para Pedidos
$app->group('/pedidos', function (RouteCollectorProxy $group) use ($secretKey) {
    $group->post('/crear', \PedidoController::class . ':CrearPedido')
          ->add(new RolMiddleware(['mozo', 'administrador'], $secretKey));

    $group->get('/listar', \PedidoController::class . ':ListarPedidos'); // Listar todos los pedidos

    $group->get('/obtener/{id}', \PedidoController::class . ':ObtenerPedido'); // Obtener un pedido específico

    $group->post('/modificar/estado/{id}', \PedidoController::class . ':CambiarEstadoPedido'); // Cambiar el estado de un pedido

    $group->get('/listar/estado', \PedidoController::class . ':ListarPedidosPorEstado'); // Listar pedidos por estado

    $group->delete('/borrar/{id}', \PedidoController::class . ':BorrarPedido')
          ->add(new RolMiddleware(['administrador'], $secretKey));
});

// Ruta de bienvenida general
$app->get('/', function (Request $request, Response $response) {
    $payload = json_encode(array("mensaje" => "Bienvenido a la API de Restaurante"));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

// Middleware para manejar rutas no definidas
$app->map(['GET', 'POST', 'PUT', 'DELETE'], '/{routes:.+}', function ($request, $response, $args) {
    $payload = json_encode(["mensaje" => "ERROR: Ruta no definida."]);
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
});

// Ejecutar la aplicación
$app->run();