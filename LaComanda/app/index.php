<?php
date_default_timezone_set("America/Argentina/Buenos_Aires");

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
require_once './middlewares/ValidacionesMiddleware.php';
require_once './utils/AutentificadorJWT.php';

require_once './controllers/EmpleadoController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Set base path
$app->setBasePath('/LaComanda/app');


// $app->group('/empleados', function (RouteCollectorProxy $group) 
// {
//   $group->get('[/]', \EmpleadoController::class . ':TraerTodos');
//   $group->get('/{empleado}', \EmpleadoController::class . ':TraerUno');
//   $group->post('[/]', \EmpleadoController::class . ':CargarUno');
// });

// $app->group('/productos', function (RouteCollectorProxy $group) 
// {
//   $group->get('[/]', \ProductoController::class . ':TraerTodos');
//   $group->get('/{idProducto}', \ProductoController::class . ':TraerUno');
//   $group->post('[/]', \ProductoController::class . ':CargarUno');
// });

// $app->group('/mesas', function (RouteCollectorProxy $group) 
// {
//   $group->get('[/]', \MesaController::class . ':TraerTodas');
//   $group->get('/{idMesa}', \MesaController::class . ':TraerMesa');
//   $group->post('[/]', \MesaController::class . ':CargarUno');
// });

// $app->group('/pedidos', function (RouteCollectorProxy $group) 
// {
//   $group->get('[/]', \PedidoController::class . ':TraerTodos');
//   $group->get('/{pedido}', \PedidoController::class . ':TraerUno');
//   $group->post('[/]', \PedidoController::class . ':CargarUno');
// });


// Routes
$app->group('/auth', function (RouteCollectorProxy $group) {

  $group->post('[/login]', \EmpleadoController::class . ':LogueoEmpleado')->add(\ValidacionesMiddleware::class . ':DatosLogin');
});

$app->group('/mesas', function (RouteCollectorProxy $group) 
{
  $group->get('[/listar]', \MesaController::class . ':TraerTodas')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');
  $group->get('/mesaMasUsada', \MesaController::class . ':TraerMesaMasUsada')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('/cobrar/{nroMesa}', \MesaController::class . ':CobrarMesa')->add(\ValidacionesMiddleware::class . ':RolMozo')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('/cerrar/{nroMesa}', \MesaController::class . ':CerrarMesa')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');
  $group->post('[/asignarCliente]', \MesaController::class . ':AsignarCliente')->add(\ValidacionesMiddleware::class . ':DatosNuevoCliente')->add(\ValidacionesMiddleware::class . ':RolMozo')->add(\ValidacionesMiddleware::class . ':Token');
  $group->post('/tomarFoto', \MesaController::class . ':TomarFotoMesa')->add(\ValidacionesMiddleware::class . ':DatosFotoMesa')->add(\ValidacionesMiddleware::class . ':RolMozo')->add(\ValidacionesMiddleware::class . ':Token');
  $group->post('/responderEncuesta/{nroMesa}', \MesaController::class . ':ResponderEncuesta');
});

$app->group('/productos', function (RouteCollectorProxy $group) 
{
  $group->post('[/cargarListaCsv]', \ProductoController::class . ':CargarListaCsv')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');
  $group->get('[/traerListaCsv]', \ProductoController::class . ':TraerListaCsv')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');
});

$app->group('/pedido', function (RouteCollectorProxy $group) 
{
  $group->post('[/nuevoPedido]', \PedidoController::class . ':NuevoPedido')->add(\ValidacionesMiddleware::class . ':DatosPedido')->add(\ValidacionesMiddleware::class . ':RolMozo')->add(\ValidacionesMiddleware::class . ':Token');
  $group->get('[/tiempoRestante]', \PedidoController::class . ':TiempoRestante');
  $group->get('/listarTodos', \ProductoController::class . ':ListadoCompleto')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('/entregarPedido', \PedidoController::class . ':EntregarPedido')->add(\ValidacionesMiddleware::class . ':RolMozo')->add(\ValidacionesMiddleware::class . ':Token');
});

$app->group('/barra', function (RouteCollectorProxy $group) 
{
  $group->get('[/pedidosPendientes]', \PedidoController::class . ':PedidosPendBarra')->add(\ValidacionesMiddleware::class . ':RolBartender')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('[/prepararPedido]', \PedidoController::class . ':PrepararPedido')->add(\ValidacionesMiddleware::class . ':RolBartender')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('/terminarPedido', \PedidoController::class . ':TerminarPedido')->add(\ValidacionesMiddleware::class . ':RolBartender')->add(\ValidacionesMiddleware::class . ':Token');
});

$app->group('/cerveceria', function (RouteCollectorProxy $group) 
{
  $group->get('[/pedidosPendientes]', \PedidoController::class . ':PedidosPendCerveceria')->add(\ValidacionesMiddleware::class . ':RolCervecero')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('[/prepararPedido]', \PedidoController::class . ':PrepararPedido')->add(\ValidacionesMiddleware::class . ':RolCervecero')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('/terminarPedido', \PedidoController::class . ':TerminarPedido')->add(\ValidacionesMiddleware::class . ':RolCervecero')->add(\ValidacionesMiddleware::class . ':Token');
});

$app->group('/cocina', function (RouteCollectorProxy $group) 
{
  $group->get('[/pedidosPendientes]', \PedidoController::class . ':PedidosPendCocina')->add(\ValidacionesMiddleware::class . ':RolCocinero')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('[/prepararPedido]', \PedidoController::class . ':PrepararPedido')->add(\ValidacionesMiddleware::class . ':RolCocinero')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('/terminarPedido', \PedidoController::class . ':TerminarPedido')->add(\ValidacionesMiddleware::class . ':RolCocinero')->add(\ValidacionesMiddleware::class . ':Token');
});

$app->group('/candybar', function (RouteCollectorProxy $group) 
{
  $group->get('[/pedidosPendientes]', \PedidoController::class . ':PedidosPendCandy')->add(\ValidacionesMiddleware::class . ':RolCocinero')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('[/prepararPedido]', \PedidoController::class . ':PrepararPedido')->add(\ValidacionesMiddleware::class . ':RolCocinero')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('/terminarPedido', \PedidoController::class . ':TerminarPedido')->add(\ValidacionesMiddleware::class . ':RolCocinero')->add(\ValidacionesMiddleware::class . ':Token');
});

$app->run();
