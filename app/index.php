<?php
/*
 Este archivo inicializa el entorno de Slim, 
 carga las dependencias y define las rutas de 
 la API que se mapearán a los métodos del 
 controlador UsuarioController.
*/
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
// require_once './middlewares/Logger.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/EmpleadoController.php';
require_once './controllers/MesaController.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Set base path
$app->setBasePath('/app');

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{usuario}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
});

$app->get('[/]', function (Request $request, Response $response) {    
    $payload = json_encode(array("mensaje" => "Slim Framework 4 PHP"));
    
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
}); // http://localhost:666/app , así funciona.

$app->get('/bienvenida', function (Request $request, Response $response, $args) {
  $response->getBody()->write("¡Bienvenido a la API!");
  return $response;
}); // No funciona, porque está su ruta duplicada con la anterior

// Las acciones de Empleado: Alta, Listar y Modificar.
$app->group('/empleados', function (RouteCollectorProxy $group) {
  $group->post('[/]', \EmpleadoController::class . ':CrearEmpleado');
  $group->get('[/]', \EmpleadoController::class . ':ListarEmpleados');
  $group->post('/estado/{id}', \EmpleadoController::class . ':CambiarEstadoEmpleado');
});

// Las acciones de Mesa: Alta, Listar y Modificar.
$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->post('[/]', \MesaController::class . ':CrearMesa');
  $group->get('[/]', \MesaController::class . ':ListarMesas');
  $group->post('/estado/{id}', \MesaController::class . ':CambiarEstadoMesa');
});

$app->run();