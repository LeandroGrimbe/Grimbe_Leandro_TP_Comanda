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
require_once './utils/MYPDF.php';

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

// Routes
$app->group('/auth', function (RouteCollectorProxy $group) {

  $group->post('[/login]', \EmpleadoController::class . ':LogueoEmpleado')->add(\ValidacionesMiddleware::class . ':DatosLogin');
});

$app->group('/empleados', function (RouteCollectorProxy $group) 
{
  $group->get('[/listarTodos]', \EmpleadoController::class . ':TraerTodos')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');;
  $group->get('/descargarPDF', \EmpleadoController::class . ':ListadoPDF')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');;
  $group->put('/inhabilitar', \EmpleadoController::class . ':BajaEmpleado')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');;
});

$app->group('/mesas', function (RouteCollectorProxy $group) 
{
  $group->get('[/listar]', \MesaController::class . ':TraerTodas')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');
  $group->get('/mesaMasUsada', \MesaController::class . ':TraerMesaMasUsada')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');
  $group->get('/mejoresEncuestas', \MesaController::class . ':MejoresEncuestas')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');
  $group->get('/estadisticas', \MesaController::class . ':EstadisticasA30Dias')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('/cobrar', \MesaController::class . ':CobrarMesa')->add(\ValidacionesMiddleware::class . ':DatosMesa')->add(\ValidacionesMiddleware::class . ':RolMozo')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('/cerrar', \MesaController::class . ':CerrarMesa')->add(\ValidacionesMiddleware::class . ':DatosMesa')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');
  $group->post('[/asignarCliente]', \MesaController::class . ':AsignarCliente')->add(\ValidacionesMiddleware::class . ':DatosNuevoCliente')->add(\ValidacionesMiddleware::class . ':RolMozo')->add(\ValidacionesMiddleware::class . ':Token');
  $group->post('/tomarFoto', \MesaController::class . ':TomarFotoMesa')->add(\ValidacionesMiddleware::class . ':DatosFotoMesa')->add(\ValidacionesMiddleware::class . ':RolMozo')->add(\ValidacionesMiddleware::class . ':Token');
  $group->post('/responderEncuesta/{nroMesa}', \MesaController::class . ':ResponderEncuesta');
});

$app->group('/productos', function (RouteCollectorProxy $group) 
{
  $group->post('[/cargarListaCsv]', \ProductoController::class . ':CargarListaCsv')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');
  $group->get('[/traerListaCsv]', \ProductoController::class . ':TraerListaCsv')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');
  $group->get('/listarTodos', \ProductoController::class . ':TraerTodos')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');
});

$app->group('/pedidos', function (RouteCollectorProxy $group) 
{
  $group->post('[/nuevoPedido]', \PedidoController::class . ':NuevoPedido')->add(\ValidacionesMiddleware::class . ':DatosPedido')->add(\ValidacionesMiddleware::class . ':RolMozo')->add(\ValidacionesMiddleware::class . ':Token');
  $group->get('[/tiempoRestante]', \PedidoController::class . ':TiempoRestante')->add(\ValidacionesMiddleware::class . ':DatosConsultaTiempo');
  $group->get('/listarTodos', \ProductoController::class . ':ListadoProductosPedido')->add(\ValidacionesMiddleware::class . ':RolSocio')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('/entregarPedido', \PedidoController::class . ':EntregarPedido')->add(\ValidacionesMiddleware::class . ':DatosEntrega')->add(\ValidacionesMiddleware::class . ':RolMozo')->add(\ValidacionesMiddleware::class . ':Token');
});

$app->group('/barra', function (RouteCollectorProxy $group) 
{
  $group->get('[/platosPendientes]', \PedidoController::class . ':PlatosPendBarra')->add(\ValidacionesMiddleware::class . ':RolBartender')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('[/prepararPlato]', \PedidoController::class . ':PrepararPlato')->add(\ValidacionesMiddleware::class . ':DatosPreparacion')->add(\ValidacionesMiddleware::class . ':RolBartender')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('/terminarPlato', \PedidoController::class . ':TerminarPlato')->add(\ValidacionesMiddleware::class . ':DatosFinalizacion')->add(\ValidacionesMiddleware::class . ':RolBartender')->add(\ValidacionesMiddleware::class . ':Token');
});

$app->group('/cerveceria', function (RouteCollectorProxy $group) 
{
  $group->get('[/platosPendientes]', \PedidoController::class . ':PlatosPendCerveceria')->add(\ValidacionesMiddleware::class . ':RolCervecero')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('[/prepararPlato]', \PedidoController::class . ':PrepararPlato')->add(\ValidacionesMiddleware::class . ':DatosPreparacion')->add(\ValidacionesMiddleware::class . ':RolCervecero')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('/terminarPlato', \PedidoController::class . ':TerminarPlato')->add(\ValidacionesMiddleware::class . ':DatosFinalizacion')->add(\ValidacionesMiddleware::class . ':RolCervecero')->add(\ValidacionesMiddleware::class . ':Token');
});

$app->group('/cocina', function (RouteCollectorProxy $group) 
{
  $group->get('[/platosPendientes]', \PedidoController::class . ':PlatosPendCocina')->add(\ValidacionesMiddleware::class . ':RolCocinero')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('[/prepararPlato]', \PedidoController::class . ':PrepararPlato')->add(\ValidacionesMiddleware::class . ':DatosPreparacion')->add(\ValidacionesMiddleware::class . ':RolCocinero')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('/terminarPlato', \PedidoController::class . ':TerminarPlato')->add(\ValidacionesMiddleware::class . ':DatosFinalizacion')->add(\ValidacionesMiddleware::class . ':RolCocinero')->add(\ValidacionesMiddleware::class . ':Token');
});

$app->group('/candybar', function (RouteCollectorProxy $group) 
{
  $group->get('[/platosPendientes]', \PedidoController::class . ':PlatosPendCandy')->add(\ValidacionesMiddleware::class . ':RolCocinero')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('[/prepararPlato]', \PedidoController::class . ':PrepararPlato')->add(\ValidacionesMiddleware::class . ':DatosPreparacion')->add(\ValidacionesMiddleware::class . ':RolCocinero')->add(\ValidacionesMiddleware::class . ':Token');
  $group->put('/terminarPlato', \PedidoController::class . ':TerminarPlato')->add(\ValidacionesMiddleware::class . ':DatosFinalizacion')->add(\ValidacionesMiddleware::class . ':RolCocinero')->add(\ValidacionesMiddleware::class . ':Token');
});

$app->run();
