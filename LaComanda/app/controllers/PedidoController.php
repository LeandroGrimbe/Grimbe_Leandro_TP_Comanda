<?php
require_once './models/Pedido.php';
require_once './controllers/ProductoController.php';
require_once './controllers/MesaController.php';
require_once './controllers/EmpleadoController.php';

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

    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);
    EmpleadoController::RegistroLog($token, "Crear pedido nro " . $id);

    $payload = json_encode(array("mensaje" => "Pedido creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function PlatosPendBarra($request, $response, $args)
  {
    $idSector = 1; //barra tragos
    $lista = ProductoController::TraerListaPedidoPend($idSector);
    if($lista)
    {
      $payload = json_encode(array("ProductosPendientesBarra" => $lista), JSON_PRETTY_PRINT);

      $header = $request->getHeaderLine('Authorization');
      $token = trim(explode("Bearer", $header)[1]);
      EmpleadoController::RegistroLog($token, "Consultar pendientes barra");
    }
    else
    {
      $payload = json_encode(array("error" => "No hay pedidos pendientes en la Barra."));
    }
    
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function PlatosPendCerveceria($request, $response, $args)
  {
    $idSector = 2; //barra cerveza
    $lista = ProductoController::TraerListaPedidoPend($idSector);
    if($lista)
    {
      $payload = json_encode(array("ProductosPendientesCerveceria" => $lista), JSON_PRETTY_PRINT);

      $header = $request->getHeaderLine('Authorization');
      $token = trim(explode("Bearer", $header)[1]);
      EmpleadoController::RegistroLog($token, "Consultar pendientes Cerv");
    }
    else
    {
      $payload = json_encode(array("error" => "No hay pedidos pendientes en la Cerveceria."));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function PlatosPendCocina($request, $response, $args)
  {
    $idSector = 3; //cocina
    $lista = ProductoController::TraerListaPedidoPend($idSector);
    if($lista)
    {
      $payload = json_encode(array("ProductosPendientesCocina" => $lista), JSON_PRETTY_PRINT);

      $header = $request->getHeaderLine('Authorization');
      $token = trim(explode("Bearer", $header)[1]);
      EmpleadoController::RegistroLog($token, "Consultar pendientes cocina");
    }
    else
    {
      $payload = json_encode(array("error" => "No hay pedidos pendientes en la Cocina."));
    }
    
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function PlatosPendCandy($request, $response, $args)
  {
    $idSector = 4; //candy bar
    $lista = ProductoController::TraerListaPedidoPend($idSector);
    if($lista)
    {
      $payload = json_encode(array("ProductosPendientesCandy" => $lista), JSON_PRETTY_PRINT);

      $header = $request->getHeaderLine('Authorization');
      $token = trim(explode("Bearer", $header)[1]);
      EmpleadoController::RegistroLog($token, "Consultar pendientes candy");
    }
    else
    {
      $payload = json_encode(array("error" => "No hay pedidos pendientes en la Candy Bar."));
    }
    
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  
  public function PrepararPlato($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $idPlato = $parametros['idPlato'];
    $tiempoPreparacion = $parametros['tiempoPreparacion'];

    ProductoController::PrepararProductoPedido($idPlato, $tiempoPreparacion);

    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);
    EmpleadoController::RegistroLog($token, "Preparar plato nro " . $idPlato);

    $mensaje = "Plato/ Bebida ya puesto/ a en preparacion. Tiempo estimado de finalizacion: " . $tiempoPreparacion;
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

    if($fechaAct >= $fechaFinalizacion && $fechaFinalizacion != "0000-00-00 00:00:00")
    {
      $mensaje = "El pedido nro " . $idPedido . ", de la mesa " . $nroMesa . " ya esta listo para servir";
    }
    else if ($fechaFinalizacion != "0000-00-00 00:00:00")
    {
      $mensaje = "El pedido nro " . $idPedido . ", de la mesa " . $nroMesa . " estara listo en " . $tiempoRestante->format('%R horas, %i minutos');
    }
    else
    {
      $mensaje = "El pedido nro " . $idPedido . ", de la mesa " . $nroMesa . " aun tiene elementos que no estan en preparacion.";
    }
    
    $payload = json_encode(array("mensaje" => $mensaje));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TerminarPlato($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $idPlato = $parametros['idPlato'];

    $fechaFinalizacion = ProductoController::TiempoRestanteProducto($idPlato);
    $fechaFinAux = new datetime($fechaFinalizacion);
    $fechaAct = new datetime(date("Y-m-d H:i:s"));

    if($fechaAct > $fechaFinalizacion)
    {
      ProductoController::FinalizarProductoPedido($idPlato);

      $mensaje = "El plato ya esta listo para servir";

      $header = $request->getHeaderLine('Authorization');
      $token = trim(explode("Bearer", $header)[1]);
      EmpleadoController::RegistroLog($token, "Terminar plato nro " . $idPlato);
    }
    else
    {
      $mensaje = "El plato solicitado aun esta en preparacion..";
    }

    $payload = json_encode(array("mensaje" => $mensaje));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function EntregarPedido($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $idPedido = $parametros['idPedido'];
    $nroMesa = $parametros['nroMesa'];

    switch(ProductoController::RevisarEstadoPlatos($idPedido))
    {
      case "Listo":
        $mensaje = "El pedido nro " . $idPedido . " se ha entregado a la mesa " . $nroMesa . ". Clientes comiendo";
        MesaController::CambiarEstado(2, $nroMesa);
        ProductoController::CambiarEstadoPlatos(4, $idPedido);

        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        EmpleadoController::RegistroLog($token, "Entregar pedido nro " . $idPedido);
        break;
      
      case "No listo":
        $mensaje = "El pedido nro " . $idPedido . " aun tiene platos en preparacion, aguarde por favor..";
        break;
      
      case "Ya entregado":
        $mensaje = "El pedido nro " . $idPedido . " ya fue entregado previamente.";
        break;
      
      case "Cancelado":
        $mensaje = "El pedido nro " . $idPedido . " fue cancelado";
        break;
    }

    $payload = json_encode(array("mensaje" => $mensaje));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function BuscarPedido($idPedido)
  {
    return Pedido::ObtenerUno($idPedido);
  }
  
  public static function BuscarPedidosMesa($idMesa)
  {
    $listaPedidos = Pedido::ObtenerTodos();
    $listaPedidosMesa = [];
    foreach($listaPedidos as $pedido)
    {
      if($pedido->idMesa == $idMesa)
      {
        $listaPedidosMesa[] = $pedido;
      }
    }

    return $listaPedidosMesa;
  }

  public static function VerificarPedidoMesa($idPedido, $idMesa)
  {
    $mesaCorrecta = false;
    $pedido = Pedido::ObtenerUno($idPedido);
    if($pedido->idMesa == $idMesa)
    {
      $mesaCorrecta = true;
    }

    return $mesaCorrecta;
  }
}
