<?php
require_once './models/Pedido.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';

class PedidoController extends Pedido
{
  public function NuevoPedido($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $mesa = MesaController::BuscarMesaPorNro($parametros['nroMesa']);

    $listaIdsProductos = $parametros['listaIdsProductos'];
    $precio = ProductoController::CalcularPrecio($listaIdsProductos);
    MesaController::SumarPedidoACuenta($parametros['nroMesa'], $precio);

    $pedido = new Pedido();
    $pedido->idMesa = $mesa->id;
    $pedido->precio = $precio;
    $pedido->idEstado = 1;
    $id = $pedido->CrearPedido();

    ProductoController::NuevaLista($id, $listaIdsProductos);

    $payload = json_encode(array("mensaje" => "Pedido creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  // public function TraerPedido($request, $response, $args)
  // {
  //   $id = $args['id'];
  //   $pedido = Pedido::ObtenerUno($id);
  //   if($pedido)
  //   {
  //     $payload = json_encode($pedido);
  //   }
  //   else
  //   {
  //     $payload = json_encode(array("error" => "Pedido no encontrado"));
  //   }

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Pedido::ObtenerTodos();
    if($lista)
    {
      $listadoPedidos = [];
      foreach($lista as $pedido)
      {
        $pedidoAux = new StdClass();
        $pedidoAux->id = $pedido->id;
        $pedidoAux->idMesa = $pedido->idMesa;

        $fechaFinalizacion = ProductoController::TiempoRestante($pedido->id);
        if($fechaFinalizacion == 0)
        {
          $pedidoAux->tiempoRestante = "No iniciado";
        }
        else
        {
          $fechaFinAux = new datetime($fechaFinalizacion);
          $fechaAct = new datetime(date("Y-m-d H:i:s"));
          $tiempoRestante = date_diff($fechaAct,$fechaFinAux);
  
          $pedidoAux->tiempoRestante = $tiempoRestante->format('%R%H Horas, %I minutos');
        }
        
        $listadoPedidos[] = $pedidoAux;
      }

      $payload = json_encode(array("listaPedidos" => $listadoPedidos));
    }
    else
    {
      $payload = json_encode(array("error" => "No hay pedidos registrados"));
    }
    
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  
  // public function ModificarPedido($request, $response, $args)
  // {
  //   $parametros = $request->getParsedBody();

  //   $id = $parametros['id'];
  //   $mesa = MesaController::BuscarMesaPorNro($parametros['nroMesa']);

  //   $listaIdsProductos = $parametros['listaIdsProductos'];
  //   $precio = ProductoController::CalcularPrecio($listaIdsProductos);

  //   $idsEstadosProducto = $parametros['idsEstadosProducto'];
  //   $tiemposPrepProductos = $parametros['tiemposPrepProductos'];

  //   if(Pedido::ObtenerUno($id))
  //   {
  //     Pedido::Modificar($id, $mesa->id, $precio);
  //     ProductoController::ModificarListaPedido($id, $listaIdsProductos, $tiemposPrepProductos, $idsEstadosProducto);

  //     $payload = json_encode(array("mensaje" => "Pedido modificado con exito"));
  //   } 
  //   else
  //   {
  //     $payload = json_encode(array("error" => "No se encontro el id, no se realizaron cambios"));
  //   }

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }

  // public function BorrarPedido($request, $response, $args)
  // {
  //   $parametros = $request->getParsedBody();

  //   $id = $parametros['id'];

  //   if(Pedido::ObtenerUno($id))
  //   {
  //     Pedido::Borrar($id);
  //     ProductoController::BorrarListaPedido($id);

  //     $payload = json_encode(array("mensaje" => "Pedido borrado/cerrado con exito"));
  //   } 
  //   else
  //   {
  //     $payload = json_encode(array("error" => "No se encontro el id, no se realizaron cambios"));
  //   }

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }

  public function PedidosPendBarra($request, $response, $args)
  {
    $idSector = 1; //barra tragos
    $lista = ProductoController::TraerListaPedidoPend($idSector);
    if($lista)
    {
      $payload = json_encode(array("ProductosPendientesBarra" => $lista));
    }
    else
    {
      $payload = json_encode(array("error" => "No hay pedidos pendientes en la Barra."));
    }
    
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function PedidosPendCerveceria($request, $response, $args)
  {
    $idSector = 2; //barra cerveza
    $lista = ProductoController::TraerListaPedidoPend($idSector);
    if($lista)
    {
      $payload = json_encode(array("ProductosPendientesCerveceria" => $lista));
    }
    else
    {
      $payload = json_encode(array("error" => "No hay pedidos pendientes en la Cerveceria."));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function PedidosPendCocina($request, $response, $args)
  {
    $idSector = 3; //cocina
    $lista = ProductoController::TraerListaPedidoPend($idSector);
    if($lista)
    {
      $payload = json_encode(array("ProductosPendientesCocina" => $lista));
    }
    else
    {
      $payload = json_encode(array("error" => "No hay pedidos pendientes en la Cocina."));
    }
    
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function PedidosPendCandy($request, $response, $args)
  {
    $idSector = 4; //candy bar
    $lista = ProductoController::TraerListaPedidoPend($idSector);
    if($lista)
    {
      $payload = json_encode(array("ProductosPendientesCandy" => $lista));
    }
    else
    {
      $payload = json_encode(array("error" => "No hay pedidos pendientes en la Candy Bar."));
    }
    
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  
  public function PrepararPedido($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $id = $parametros['id'];
    $tiempoPreparacion = $parametros['tiempoPreparacion'];

    ProductoController::PrepararProductoPedido($id, $tiempoPreparacion);

    $mensaje = "Pedido ya puesto en preparacion. Tiempo estimado de finalizacion: " . $tiempoPreparacion;
    $payload = json_encode(array("mensaje" => $mensaje));
    
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TiempoRestante($request, $response, $args)
  {
    $parametros = $request->getQueryParams();

    $idPedido = $parametros['idPedido'];
    $nroMesa = $parametros['nroMesa'];

    $fechaFinalizacion = ProductoController::TiempoRestante($idPedido);
    $fechaFinAux = new datetime($fechaFinalizacion);
    $fechaAct = new datetime(date("Y-m-d H:i:s"));
    $tiempoRestante = date_diff($fechaAct,$fechaFinAux);

    $mensaje = "El pedido nro " . $idPedido . ", de la mesa " . $nroMesa . " estara listo en " . $tiempoRestante->format('%R%i minutos');
    $payload = json_encode(array("mensaje" => $mensaje));
    
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TerminarPedido($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $id = $parametros['id'];

    $fechaFinalizacion = ProductoController::TiempoRestanteProducto($id);
    $fechaFinAux = new datetime($fechaFinalizacion);
    $fechaAct = new datetime(date("Y-m-d H:i:s"));

    if($fechaAct > $fechaFinalizacion)
    {
      ProductoController::FinalizarProductoPedido($id);

      $mensaje = "El producto ya listo para servir";
    }
    else
    {
      $mensaje = "El pedido solicitado aun esta en preparacion..";
    }

    $payload = json_encode(array("mensaje" => $mensaje));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function EntregarPedido($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $id = $parametros['id'];
    $nroMesa = $parametros['nroMesa'];

    if(ProductoController::RevisarEstado($id) == "listo")
    {
      $mensaje = "El pedido se ha entregado a la mesa";
      MesaController::CambiarEstado(2, $nroMesa);
    }
    else
    {
      $mensaje = "El pedido solicitado aun esta en preparacion..";
    }

    $payload = json_encode(array("mensaje" => $mensaje));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}
