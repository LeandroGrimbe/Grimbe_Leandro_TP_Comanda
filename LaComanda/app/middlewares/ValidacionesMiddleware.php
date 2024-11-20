<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

require_once './controllers/MesaController.php';
require_once './utils/AutentificadorJWT.php';

class ValidacionesMiddleware
{
  public function DatosLogin(Request $request, RequestHandler $handler): Response
  {   
    $parametros = $request->getParsedBody();

    if(isset($parametros['usuario']) && isset($parametros['clave']))
    {
      $response = $handler->handle($request);
    } 
    else 
    {
      $response = new Response();
      $payload = json_encode(array('mensaje' => 'Se requiere del usuario y clave para iniciar sesion. Reintente..'));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function DatosNuevoCliente(Request $request, RequestHandler $handler): Response
  {   
    $parametros = $request->getParsedBody();

    if (isset($parametros["nombreCliente"]))
    {
      $response = $handler->handle($request);
    } 
    else 
    {
      $response = new Response();
      $payload = json_encode(array('mensaje' => 'No se pudo asignar mesa al cliente, datos faltantes o mal cargados. Reintente'));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function DatosPedido(Request $request, RequestHandler $handler): Response
  {
    $parametros = $request->getParsedBody();

    $falloLista = true;
    $mensajeFalloLista = "Lista de productos inexistente o invalida, no se pudo generar el pedido..";
    if(isset($parametros["listaIdsProductos"]) && is_array($parametros["listaIdsProductos"]))
    {
      $falloLista = false;
      foreach($parametros["listaIdsProductos"] as $idProducto)
      {
        if(!is_numeric($idProducto) || !ProductoController::VerificarProducto($idProducto))
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
      if(MesaController::BuscarMesaPorNro($parametros["nroMesa"]))
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

  public function DatosFotoMesa(Request $request, RequestHandler $handler): Response
  {
    $datosValidos = false;
    if(isset($_FILES["ImagenMesa"]["tmp_name"]) && is_uploaded_file($_FILES["ImagenMesa"]["tmp_name"]))
    {
      $parametros = $request->getQueryParams();
      $nroMesa = $parametros['nroMesa'];
      if(is_numeric($nroMesa) && MesaController::BuscarMesaPorNro($nroMesa))
      {
        $datosValidos = true;
      }
    }
    
    if($datosValidos)
    {
      $response = $handler->handle($request);
    }
    else
    {
      $response = new Response();
      $payload = json_encode(array('mensaje' => 'Hubo errores al subir la foto del cliente. No se realizaron cambios..'));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function Token(Request $request, RequestHandler $handler): Response
  {   
    $header = $request->getHeaderLine('Authorization');
    
    if($header)
    {
      $token = trim(explode("Bearer", $header)[1]);
      //try {
          AutentificadorJWT::VerificarToken($token);
          $response = $handler->handle($request);
      //} catch (Exception $e) {
      //    $response = new Response();
      //    $payload = json_encode(array('error' => $e)); //'Hubo un error con el token, reingrese o inicie sesion nuevamente..'
      //    $response->getBody()->write($payload);
      //}
    }
    else
    {
      $response = new Response();
      $payload = json_encode(array('error' => 'No estas logueado, inicia sesion antes de realizar cualquier accion'));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function RolMozo(Request $request, RequestHandler $handler): Response
  {   
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);

    $data = AutentificadorJWT::ObtenerData($token);
    
    if($data->idRol == 4)
    {
      $response = $handler->handle($request);
    }
    else
    {
      $response = new Response();
      $payload = json_encode(array('error' => 'No estas autorizado a realizar esta accion. Revisar'));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function RolBartender(Request $request, RequestHandler $handler): Response
  {   
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);

    $data = AutentificadorJWT::ObtenerData($token);
    if($data->idRol == 1)
    {
      $response = $handler->handle($request);
    }
    else
    {
      $response = new Response();
      $payload = json_encode(array('error' => 'No estas autorizado a realizar esta accion. Revisar'));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function RolCervecero(Request $request, RequestHandler $handler): Response
  {   
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);

    $data = AutentificadorJWT::ObtenerData($token);
    if($data->idRol == 2)
    {
      $response = $handler->handle($request);
    }
    else
    {
      $response = new Response();
      $payload = json_encode(array('error' => 'No estas autorizado a realizar esta accion. Revisar'));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function RolCocinero(Request $request, RequestHandler $handler): Response
  {   
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);

    $data = AutentificadorJWT::ObtenerData($token);
    if($data->idRol == 3)
    {
      $response = $handler->handle($request);
    }
    else
    {
      $response = new Response();
      $payload = json_encode(array('error' => 'No estas autorizado a realizar esta accion. Revisar'));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function RolSocio(Request $request, RequestHandler $handler): Response
  {   
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);

    $data = AutentificadorJWT::ObtenerData($token);
    if($data->idRol == 5)
    {
      $response = $handler->handle($request);
    }
    else
    {
      $response = new Response();
      $payload = json_encode(array('error' => 'No estas autorizado a realizar esta accion. Revisar'));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }
}

