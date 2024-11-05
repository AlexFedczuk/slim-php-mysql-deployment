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
require_once './middlewares/RolMiddleware.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instanciar la aplicación
$app = AppFactory::create();
$app->setBasePath('/app'); // Definir base path

// Middleware de errores
$app->addErrorMiddleware(true, true, true);

// Middleware para parseo de cuerpo
$app->addBodyParsingMiddleware();

// Ruta de bienvenida general
$app->get('/', function (Request $request, Response $response) {
    $payload = json_encode(array("mensaje" => "Bienvenido a la API de Restaurante"));
    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});

// Rutas para Usuarios
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{usuario}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
});

// Definir roles permitidos para la ruta
$rolesPermitidos = ['socio', 'administrador'];

// Rutas para Empleados
$app->group('/empleados', function (RouteCollectorProxy $group) {
    $group->post('[/]', \EmpleadoController::class . ':CrearEmpleado'); // Alta de empleado
    $group->get('[/]', \EmpleadoController::class . ':ListarEmpleados')->add(new VerificarRolMiddleware()); // Agrega el middleware aquí; // Listar empleados
    $group->post('/estado/{id}', \EmpleadoController::class . ':CambiarEstadoEmpleado'); // Cambiar estado
});

// Rutas para Productos
$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->post('[/]', \ProductoController::class . ':CrearProducto');  // Crear un nuevo producto
  $group->get('[/]', \ProductoController::class . ':ListarProductos'); // Listar todos los productos
});

// Rutas para Mesas
$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->post('[/]', \MesaController::class . ':CrearMesa'); // Alta de mesa
    $group->get('[/]', \MesaController::class . ':ListarMesas'); // Listar mesas
    $group->post('/estado/{id}', \MesaController::class . ':CambiarEstadoMesa'); // Cambiar estado
    $group->get('/informe', \MesaController::class . ':ObtenerInformeDeUsoDeMesas');
});

// Rutas para Pedidos
$app->group('/pedidos', function (RouteCollectorProxy $group) {
    $group->post('[/]', \PedidoController::class . ':CrearPedido'); // Crear un nuevo pedido
    $group->get('/todos', \PedidoController::class . ':ListarTodosLosPedidos'); // Listar todos los pedidos
    $group->get('/{id}', \PedidoController::class . ':ObtenerPedido'); // Obtener un pedido específico
    $group->post('/estado/{id}', \PedidoController::class . ':CambiarEstadoPedido'); // Cambiar el estado de un pedido
    $group->get('[/]', \PedidoController::class . ':ListarPedidosPorEstado'); // Listar pedidos por estado
});

// Ejecutar la aplicación
$app->run();