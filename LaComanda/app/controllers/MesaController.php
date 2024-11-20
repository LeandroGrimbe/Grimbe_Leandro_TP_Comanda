<?php
require_once './models/Mesa.php';

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

    $mensaje = "Cliente asignado correctamente a la mesa nro " . $mesa->nroMesa . ". Esperando pedidos";
    $payload = json_encode(array("mensaje" => $mensaje));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  // public function TraerMesa($request, $response, $args)
  // {
  //   $id = $args['id'];
  //   $mesa = Mesa::ObtenerUna($id);
  //   if($mesa)
  //   {
  //     $payload = json_encode($mesa);
  //   }
  //   else
  //   {
  //     $payload = json_encode(array("error" => "Mesa no encontrada"));
  //   }

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }

  public function TraerTodas($request, $response, $args)
  {
    $lista = Mesa::ObtenerTodas();
    if($lista)
    {
      $payload = json_encode(array("listaMesas" => $lista));
    }
    else
    {
      $payload = json_encode(array("error" => "No hay mesas registradas"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  // public function ModificarMesa($request, $response, $args)
  // {
  //   $parametros = $request->getParsedBody();

  //   $id = $parametros['id'];
  //   $nombreCliente = $parametros['nombreCliente'];
  //   $pathFoto = $parametros['pathFoto'];
  //   $nroMesa = $parametros['nroMesa'];
  //   $cuenta = $parametros['cuenta'];
  //   $fecha = $parametros['fecha'];
  //   $idEstadoMesa = $parametros['idEstadoMesa'];

  //   if(Mesa::ObtenerUna($id)) 
  //   {
  //     Mesa::Modificar($id, $nombreCliente, $pathFoto, $nroMesa, $cuenta, $fecha, $idEstadoMesa);
  //     $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));
  //   } 
  //   else
  //   {
  //     $payload = json_encode(array("error" => "No se encontro el id, no se realizaron cambios"));
  //   }

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }

  // public function BorrarMesa($request, $response, $args)
  // {
  //   $parametros = $request->getParsedBody();

  //   $id = $parametros['id'];
    
  //   if(Mesa::ObtenerUna($id)) 
  //   {
  //     Mesa::Borrar($id);
  //     $payload = json_encode(array("mensaje" => "Mesa borrada con exito"));
  //   } 
  //   else
  //   {
  //     $payload = json_encode(array("error" => "No se encontro el id, no se realizaron cambios"));
  //   }

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }

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

    $payload = json_encode(array("mensaje" => "Foto de mesa guardada con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function CobrarMesa($request, $response, $args)
  {
    $nroMesa = $args['nroMesa'];

    $mesa = Mesa::ObtenerMesaPorNro($nroMesa);
    if($mesa) 
    {
      Mesa::ActualizarEstado(3, $nroMesa);
      $payload = json_encode(array("mensaje" => "Se ha cobrado el monto de: " . $mesa->cuenta . " pesos a la mesa " . $nroMesa));
    } 
    else
    {
      $payload = json_encode(array("error" => "No se encontro la mesa, no se realizaron cambios"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function CerrarMesa($request, $response, $args)
  {
    $nroMesa = $args['nroMesa'];

    $mesa = Mesa::ObtenerMesaPorNro($nroMesa);
    if($mesa) 
    {
      Mesa::ActualizarEstado(4, $nroMesa);
      $payload = json_encode(array("mensaje" => "Se ha cerrado la cuenta de la mesa " . $nroMesa));
    } 
    else
    {
      $payload = json_encode(array("error" => "No se encontro la mesa, no se realizaron cambios"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ResponderEncuesta($request, $response, $args)
  {
    // $parametros = $request->getParsedBody();
    // $nroMesa = $args['nroMesa'];

    // $id = $parametros['puntuacionMesa'];
    // $nombreCliente = $parametros['puntuacionRestaurante'];
    // $pathFoto = $parametros['puntuacionMozo'];
    // $nroMesa = $parametros['puntuacionCocinero'];
    // $cuenta = $parametros['comentarios'];

    $payload = json_encode(array("mensaje" => "Gracias por su opinion!"));
    
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
          $objMesa = new StdObject();
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
