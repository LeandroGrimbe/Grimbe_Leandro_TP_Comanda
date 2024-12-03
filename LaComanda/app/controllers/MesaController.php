<?php
require_once './models/Mesa.php';
require_once './controllers/ProductoController.php';
require_once './controllers/EmpleadoController.php';
require_once './controllers/PedidoController.php';

class MesaController extends Mesa
{
  public function AsignarCliente($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $mesa = new Mesa();
    $mesa->nombreCliente = $parametros['nombreCliente'];
    $mesa->pathFoto = "";
    $mesa->nroMesa = self::ObtenerMesaLibre();
    $mesa->cuenta = 0;
    $mesa->fecha = date("Y-m-d");
    $mesa->idEstadoMesa = 1;
    $mesa->OcuparMesa();

    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);
    EmpleadoController::RegistroLog($token, "Ocupar Mesa nro " . $mesa->nroMesa);

    $mensaje = "Cliente asignado correctamente a la mesa nro " . $mesa->nroMesa . ". Esperando pedidos";
    $payload = json_encode(array("mensaje" => $mensaje));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodas($request, $response, $args)
  {
    $lista = Mesa::ObtenerTodas();
    if($lista)
    {
      $payload = json_encode(array("listaMesas" => $lista), JSON_PRETTY_PRINT);

      $header = $request->getHeaderLine('Authorization');
      $token = trim(explode("Bearer", $header)[1]);
      EmpleadoController::RegistroLog($token, "Listar Mesas");
    }
    else
    {
      $payload = json_encode(array("error" => "No hay mesas registradas"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TomarFotoMesa($request, $response, $args)
  {
    $pathImagenCliente = "./fotosClientes/";
    if (!file_exists($pathImagenCliente)) 
    {
        mkdir($pathImagenCliente, 0777, true);
    }

    $parametros = $request->getQueryParams();
    $nroMesa = $parametros['nroMesa'];
    $mesa = self::BuscarMesaPorNro($nroMesa);

    $destino = $pathImagenCliente . $mesa->nombreCliente . "_" . $nroMesa . "_" . $mesa->fecha . ".png"; 
    move_uploaded_file($_FILES["ImagenMesa"]["tmp_name"], $destino);

    Mesa::Modificar($mesa->id, $mesa->nombreCliente, $destino, $nroMesa, $mesa->cuenta, $mesa->fecha, $mesa->idEstadoMesa);

    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);
    EmpleadoController::RegistroLog($token, "Tomar foto mesa nro " . $nroMesa);
    
    $payload = json_encode(array("mensaje" => "Foto de mesa guardada con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function CobrarMesa($request, $response, $args)
  {
    $mensaje = "";
    $parametros = $request->getQueryParams();
    $nroMesa = $parametros['nroMesa'];

    $mesa = Mesa::ObtenerMesaPorNro($nroMesa);

    if($mesa->idEstadoMesa == 3)
    {
      $mensaje = "La mesa nro " . $nroMesa . " ya tiene la cuenta paga. No se realizaron cambios";
    }
    else if ($mesa->idEstadoMesa == 2)
    {
      $pendiente = false;
      $pedidosMesa = PedidoController::BuscarPedidosMesa($mesa->id);
      foreach($pedidosMesa as $pedido)
      {
        $estado = ProductoController::RevisarEstadoPlatos($pedido->id);
        if($estado != "Ya entregado")
        {
          $pendiente = true;
          break;
        }
      }

      if($pendiente)
      {
        $mensaje = "La mesa nro " . $nroMesa . " tiene pedidos pendientes. No se realizaron cambios";
      }
      else
      {
        Mesa::ActualizarEstado(3, $nroMesa);
        $mensaje = "Se ha cobrado el monto de: " . $mesa->cuenta . " pesos a la mesa " . $nroMesa;

        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        EmpleadoController::RegistroLog($token, "Cobrar Mesa nro " . $nroMesa);
      }
    }
    else
    {
      $mensaje = "La mesa nro " . $nroMesa . " aun no comenzo a comer. No se realizaron cambios";
    }

    $payload = json_encode(array("mensaje" => $mensaje));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function CerrarMesa($request, $response, $args)
  {
    $mensaje = "";
    $parametros = $request->getQueryParams();
    $nroMesa = $parametros['nroMesa'];

    $mesa = Mesa::ObtenerMesaPorNro($nroMesa);

    if($mesa->idEstadoMesa == 3)
    {
      Mesa::ActualizarEstado(4, $nroMesa);
      $mensaje = "La mesa nro " . $nroMesa . " se ha cerrado correctamente";

      $header = $request->getHeaderLine('Authorization');
      $token = trim(explode("Bearer", $header)[1]);
      EmpleadoController::RegistroLog($token, "Cerrar Mesa nro " . $nroMesa);
    }
    else
    {
      $mensaje = "La mesa nro " . $nroMesa . " aun no pago la cuenta. No se realizaron cambios";
    }

    $payload = json_encode(array("mensaje" => $mensaje));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ResponderEncuesta($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $nroMesa = $args['nroMesa'];
    $puntuacionMesa = $parametros['puntuacionMesa'];
    $puntuacionRestaurante = $parametros['puntuacionRestaurante'];
    $puntuacionMozo = $parametros['puntuacionMozo'];
    $puntuacionCocinero = $parametros['puntuacionCocinero'];
    $comentarios = $parametros['comentarios'];

    $fecha = date("Y-m-d");

    Mesa::NuevaEncuesta($nroMesa, $puntuacionMesa, $puntuacionRestaurante, $puntuacionMozo, $puntuacionCocinero, $comentarios, $fecha);

    $payload = json_encode(array("mensaje" => "Gracias por su opinion!"));
    
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function MejoresEncuestas($request, $response, $args)
  {
    $listaEncuestas = Mesa::ObtenerEncuestas();
    if($listaEncuestas) 
    {
      $listaMejores = [];
      foreach($listaEncuestas as $encuesta)
      {
        if($encuesta->puntuacionMesa > 7)
        {
          $listaMejores[] = $encuesta;
        }
      }

      if($listaMejores)
      {
        $mensaje = "Los mejores comentarios fueron: \n";
        foreach($listaMejores as $encuesta)
        {
          $mensaje .= $encuesta->comentarios . "\n";
        }
      }
      else
      {
        $mensaje = "No se encontraron encuestas buenas de los pedidos registrados.";

      }

      $header = $request->getHeaderLine('Authorization');
      $token = trim(explode("Bearer", $header)[1]);
      EmpleadoController::RegistroLog($token, "Mostrar mejores encuestas");
    } 
    else
    {
      $mensaje = "No hay encuestas registradas, reintente..";
    }

    $payload = json_encode(array("mensaje" => $mensaje));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerMesaMasUsada($request, $response, $args)
  {
    $listaMesas = Mesa::ObtenerTodas();
    if($listaMesas) 
    {
      $listaNrosMesa = [];
      foreach($listaMesas as $mesa)
      {
        $existe = false;
        if($listaNrosMesa)
        {
          foreach($listaNrosMesa as $objMesa)
          {
            if($mesa->nroMesa == $objMesa->nroMesa)
            {
              $objMesa->cantidadUsos++;
              $existe = true;
              break;
            }
          }
        }
        
        if(!$existe)
        {
          $objMesa = new StdClass();
          $objMesa->nroMesa = $mesa->nroMesa;
          $objMesa->cantidadUsos = 1;

          $listaNrosMesa[] = $objMesa;
        }
      }

      $nroMesaMasUsada = 0;
      $cantidadUsos = 0;
      foreach($listaNrosMesa as $objMesa)
      {
        if($objMesa->cantidadUsos > $cantidadUsos)
        {
          $nroMesaMasUsada = $objMesa->nroMesa;
          $cantidadUsos = $objMesa->cantidadUsos;
        }
      }

      $mensaje = "La mesa mas usada fue la mesa " . $nroMesaMasUsada . ", con un total de " . $cantidadUsos . " clientes";

      $header = $request->getHeaderLine('Authorization');
      $token = trim(explode("Bearer", $header)[1]);
      EmpleadoController::RegistroLog($token, "Mostrar mesa mas usada");
    } 
    else
    {
      $mensaje = "No hay mesas registradas, reintente..";
    }

    $payload = json_encode(array("mensaje" => $mensaje));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function EstadisticasA30Dias($request, $response, $args)
  {
    $listaMesas = Mesa::ObtenerTodas();
    if($listaMesas) 
    {
      $cantidadClientes = 0;
      $totalRecaudado = 0;
      $fechaActual = new datetime(date("Y-m-d H:i:s"));
      foreach($listaMesas as $mesa)
      {
        $fechaVenta = new datetime($mesa->fecha);
        $diferenciaDias = date_diff($fechaActual, $fechaVenta);

        if($diferenciaDias->format("%a") < 30 && $mesa->idEstadoMesa != 5)
        {
          $cantidadClientes++;
          $totalRecaudado += $mesa->cuenta;
        }
      }

      $mensaje = "En los ultimos 30 dias se han atendido a " . $cantidadClientes . " clientes, y se ha recaudado un total de " . $totalRecaudado . " pesos.";

      $header = $request->getHeaderLine('Authorization');
      $token = trim(explode("Bearer", $header)[1]);
      EmpleadoController::RegistroLog($token, "Mostrar estadisticas de clientes a 30 dias");
    } 
    else
    {
      $mensaje = "No hay mesas registradas, reintente..";
    }

    $payload = json_encode(array("mensaje" => $mensaje));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function SumarPedidoACuenta($nroMesa, $monto)
  {
    $mesa = Mesa::ObtenerMesaPorNro($nroMesa);
    $nuevaCuenta = $mesa->cuenta + $monto;
    Mesa::ActualizarCuenta($nroMesa, $nuevaCuenta);
  }

  public static function ObtenerMesaLibre()
  {
    $nroMesa = -1;
    do
    {
      $nroMesa = rand(10000,99999);
    }
    while(Mesa::ObtenerMesaPorNro($nroMesa));

    return $nroMesa;
  }

  public static function BuscarMesaPorNro($nroMesa)
  {
    return Mesa::ObtenerMesaPorNro($nroMesa);
  }

  public static function CambiarEstado($idEstado, $nroMesa)
  {
    Mesa::ActualizarEstado($idEstado, $nroMesa);
  }
}
