<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

require_once './controllers/MesaController.php';

class ValidacionesMiddleware
{
  public function VerificarDatosVenta(Request $request, RequestHandler $handler): Response
  {   
    $parametros = $request->getParsedBody();

    if (isset($parametros["nombreCliente"]) && isset($parametros["pathFoto"])) 
    {
      $response = $handler->handle($request);
    } 
    else 
    {
      $response = new Response();
      $payload = json_encode(array('mensaje' => 'No se pudo generar la venta, datos faltantes o mal cargados. Reintente'));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function VerificarDatosPedido(Request $request, RequestHandler $handler): Response
  {
    $parametros = $request->getParsedBody();

    $falloLista = true;
    $mensajeFalloLista = "Lista de productos inexistente o invalida, no se pudo generar el pedido..";
    if(isset($parametros["listaIdsProductos"]) && is_array($parametros["listaIdsProductos"]))
    {
      $falloLista = false;
      foreach($parametros["listaIdsProductos"] as $idProducto)
      {
        if(!is_numeric($idProducto))
        {
          $falloLista = true;
          break;
        }
      }
    }

    $falloNro = true;
    $mensajeFalloNro = "Nro de mesa inexistente o invalido, no se pudo generar el pedido..";
    if(isset($parametros["nroMesa"]) && is_numeric($parametros["nroMesa"]) && strlen((string)$parametros["nroMesa"]) == 5)
    {
      if(MesaController::BuscarMesa($parametros["nroMesa"]))
      {
        $falloNro = false;
      }
      else
      {
        $mensajeFalloNro = "No hay mesas activas registradas con ese nro, no se pudo generar el pedido..";
      }
    }

    if ($falloLista == false && $falloNro == false) 
    {
      $response = $handler->handle($request);
    } 
    else if ($falloLista == false && $falloNro == true)
    {
      $response = new Response();
      $payload = json_encode(array('mensaje' => $mensajeFalloNro));
      $response->getBody()->write($payload);
    }
    else if ($falloLista == true && $falloNro == false)
    {
      $response = new Response();
      $payload = json_encode(array('mensaje' => $mensajeFalloLista));
      $response->getBody()->write($payload);
    }
    else
    {
      $response = new Response();
      $payload = json_encode(array('mensaje' => "Hubo problemas al generar el pedido, revise los datos cargados.."));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }
}

