<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

require_once './controllers/MesaController.php';
require_once './controllers/ProductoController.php';
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

  public function Token(Request $request, RequestHandler $handler): Response
  {   
    $header = $request->getHeaderLine('Authorization');
    
    if($header)
    {
      $token = trim(explode("Bearer", $header)[1]);
      //try 
      //{
        AutentificadorJWT::VerificarToken($token);
        $response = $handler->handle($request);
      // } 
      // catch (Exception $e) 
      // {
      //   $response = new Response();
      //   $payload = json_encode(array('error' => "Hubo un error con el token, reingrese o inicie sesion nuevamente"));
      //   $response->getBody()->write($payload);
      // }
    }
    else
    {
      $response = new Response();
      $payload = json_encode(array('error' => 'No estas logueado, inicia sesion antes de realizar cualquier accion'));
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
    $mensaje = "";
    if(isset($_FILES["ImagenMesa"]["tmp_name"]) && is_uploaded_file($_FILES["ImagenMesa"]["tmp_name"]))
    {
      $parametros = $request->getQueryParams();
      if(isset($parametros['nroMesa']) && is_numeric($parametros['nroMesa']) && MesaController::BuscarMesaPorNro($parametros['nroMesa']))
      {
        $datosValidos = true;
      }
      else
      {
        $mensaje = "Nro de mesa invalido o inexistente, no se realizaron cambios..";
      }
    }
    else
    {
      $mensaje = "Imagen no cargada, no se realizaron cambios..";
    }
    
    if($datosValidos)
    {
      $response = $handler->handle($request);
    }
    else
    {
      $response = new Response();
      $payload = json_encode(array('mensaje' => $mensaje));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function DatosPreparacion(Request $request, RequestHandler $handler): Response
  {
    $parametros = $request->getParsedBody();
    $header = $request->getHeaderLine('Authorization');

    $falla = true;
    $mensaje = "";
    if (isset($parametros["idPlato"]) && isset($parametros["tiempoPreparacion"]))
    {
      $tiempoPreparacion = explode("-", $parametros["tiempoPreparacion"]);
      if(checkdate((int)$tiempoPreparacion[1], (int)$tiempoPreparacion[2], (int)$tiempoPreparacion[0]) && is_numeric($parametros["idPlato"])) //fecha: AAAA-MM-DD HH:MM
      {
        $plato = ProductoController::BuscarProductoPedido($parametros["idPlato"]);
        if($plato && $plato->idEstadoPedido == 1)
        {
          $token = trim(explode("Bearer", $header)[1]);
          $dataEmpleado = AutentificadorJWT::ObtenerData($token);

          if(($dataEmpleado->idRol == $plato->idCategoria && $dataEmpleado->idRol < 4) || ($dataEmpleado->idRol == 3 && $plato->idCategoria == 4))
          {
            $response = $handler->handle($request);
            $falla = false;
          }
          else
          {
            $mensajeError = "No estas autorizado a realizar esta accion. Revisar";
          }
        }
        else
        {
          $mensajeError = "Plato inexistente o ya procesado. Revisar";
        }
      }
      else
      {
        $mensajeError = "Datos invalidos para la peticion a realizar. Revisar";
      }
    } 
    else 
    {
      $mensajeError = "Datos faltantes para la peticion a realizar. Revisar";
    }

    if($falla)
    {
      $response = new Response();
      $payload = json_encode(array('mensaje' => $mensajeError));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function DatosConsultaTiempo(Request $request, RequestHandler $handler): Response
  {
    $datosValidos = false;
    $mensaje = "";
    $parametros = $request->getQueryParams();
    if(isset($parametros['idPedido']) && isset($parametros['nroMesa']) && is_numeric($parametros['nroMesa']) && MesaController::BuscarMesaPorNro($parametros['nroMesa']) && PedidoController::BuscarPedido($parametros['idPedido']))
    {
      $idPedido = $parametros['idPedido'];
      $mesa = MesaController::BuscarMesaPorNro($parametros['nroMesa']);

      if(PedidoController::VerificarPedidoMesa($idPedido, $mesa->id))
      {
        $datosValidos = true;
      }
      else
      {
        $mensaje = "El nro de pedido no corresponde a la mesa ingresada. Reintente";
      }
    }
    else
    {
      $mensaje = "Datos invalidos o inexistentes. Reintente..";
    }
    
    if($datosValidos)
    {
      $response = $handler->handle($request);
    }
    else
    {
      $response = new Response();
      $payload = json_encode(array('mensaje' => $mensaje));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function DatosFinalizacion(Request $request, RequestHandler $handler): Response
  {
    $parametros = $request->getParsedBody();
    $header = $request->getHeaderLine('Authorization');

    $falla = true;
    $mensaje = "";
    if (isset($parametros["idPlato"]) && is_numeric($parametros["idPlato"]))
    {
      $plato = ProductoController::BuscarProductoPedido($parametros["idPlato"]);
      if($plato && $plato->idEstadoPedido == 2)
      {
        $token = trim(explode("Bearer", $header)[1]);
        $dataEmpleado = AutentificadorJWT::ObtenerData($token);

        if(($dataEmpleado->idRol == $plato->idCategoria && $dataEmpleado->idRol < 4) || ($dataEmpleado->idRol == 3 && $plato->idCategoria == 4))
        {
          $response = $handler->handle($request);
          $falla = false;
        }
        else
        {
          $mensajeError = "No estas autorizado a realizar esta accion. Revisar";
        }
      }
      else
      {
        $mensajeError = "Plato inexistente, sin iniciar, o ya entregado. Revisar";
      }
    } 
    else 
    {
      $mensajeError = "Datos invalidos para la peticion a realizar. Revisar";
    }

    if($falla)
    {
      $response = new Response();
      $payload = json_encode(array('mensaje' => $mensajeError));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function DatosEntrega(Request $request, RequestHandler $handler): Response
  {
    $datosValidos = false;
    $mensaje = "";
    $parametros = $request->getParsedBody();
    if(isset($parametros['idPedido']) && isset($parametros['nroMesa']) && is_numeric($parametros['nroMesa']) && MesaController::BuscarMesaPorNro($parametros['nroMesa']) && PedidoController::BuscarPedido($parametros['idPedido']))
    {
      $idPedido = $parametros['idPedido'];
      $mesa = MesaController::BuscarMesaPorNro($parametros['nroMesa']);

      if(PedidoController::VerificarPedidoMesa($idPedido, $mesa->id))
      {
        $datosValidos = true;
      }
      else
      {
        $mensaje = "El nro de pedido no corresponde a la mesa ingresada. Reintente";
      }
    }
    else
    {
      $mensaje = "Datos invalidos o inexistentes. Reintente..";
    }
    
    if($datosValidos)
    {
      $response = $handler->handle($request);
    }
    else
    {
      $response = new Response();
      $payload = json_encode(array('mensaje' => $mensaje));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }

  public function DatosMesa(Request $request, RequestHandler $handler): Response
  {
    $datosValidos = false;
    $parametros = $request->getQueryParams();
    $nroMesa = $parametros["nroMesa"];
    if(isset($nroMesa) && is_numeric($nroMesa) && MesaController::BuscarMesaPorNro($nroMesa))
    {
      $datosValidos = true;
    }
    else
    {
      $mensaje = "Nro de mesa invalido, inexistente, o ya cerrado, no se realizaron cambios..";
    }
    
    if($datosValidos)
    {
      $response = $handler->handle($request);
    }
    else
    {
      $response = new Response();
      $payload = json_encode(array('mensaje' => $mensaje));
      $response->getBody()->write($payload);
    }

    return $response->withHeader('Content-Type', 'application/json');
  }

//Roles
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
//
}

