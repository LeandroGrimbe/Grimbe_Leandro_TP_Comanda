<?php
require_once './models/Producto.php';
require_once './controllers/EmpleadoController.php';

class ProductoController extends Producto
{
  public function NuevoProducto($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $nombre = $parametros['nombre'];
    $precio = $parametros['precio'];
    $idCategoria = $parametros['idCategoria'];
    $idEstado = 1;

    $prod = new Producto();
    $prod->nombre = $nombre;
    $prod->precio = $precio;
    $prod->idCategoria = $idCategoria;
    $prod->idEstado = $idEstado;
    $prod->CrearProducto();

    $payload = json_encode(array("mensaje" => "Producto creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Producto::ObtenerTodos();

    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);
    EmpleadoController::RegistroLog($token, "Listar productos");

    $payload = json_encode(array("listaProductos" => $lista), JSON_PRETTY_PRINT);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerListaCsv($request, $response, $args)
  {
    $listaProductos = Producto::ObtenerTodos();

    $datosAGuardar = "";
    foreach($listaProductos as $producto)
    {
      $datosAGuardar .= $producto->nombre . "," . $producto->precio . "," . $producto->idCategoria . "," . $producto->idEstado . "\n";
    }

    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);
    EmpleadoController::RegistroLog($token, "Traer csv productos");

    $response->getBody()->write($datosAGuardar);
    $response = $response->withHeader('Content-Type', 'text/csv')
                         ->withHeader('Content-Disposition', 'attachment; filename="archivo.csv"')
                         ->withHeader('Content-Length', strlen($datosAGuardar));
    return $response;
  }
  
  public function CargarListaCsv($request, $response, $args)
  {
    $pathListados = "./ListadosCsv/";
    if (!file_exists($pathListados)) 
    {
        mkdir($pathListados, 0777, true);
    }

    $pathListadoProducto = $pathListados . "productos.csv";
    if(file_exists($pathListadoProducto))
    {
      $archivo = fopen($pathListadoProducto, "r");

      $datosProducto = explode(",", fgets($archivo));
      while(!feof($archivo))
      {
        $producto = new Producto();
        $producto->nombre = $datosProducto[0];
        $producto->precio = $datosProducto[1];
        $producto->idCategoria = $datosProducto[2];
        $producto->idEstado = $datosProducto[3];
        $producto->CrearProducto();

        $datosProducto = explode(",", fgets($archivo));
      }
      fclose($archivo);

      $payload = json_encode(array("mensaje" => "Lista de productos cargada con exito"));

      $header = $request->getHeaderLine('Authorization');
      $token = trim(explode("Bearer", $header)[1]);
      EmpleadoController::RegistroLog($token, "Cargar csv productos");
    }
    else
    {
      $payload = json_encode(array("error" => "No existe la lista a cargar, no se realizaron cambios"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ListadoProductosPedido($request, $response, $args)
  {
    $lista = Producto::ObtenerListadoCompleto();
    if($lista)
    {
      $payload = json_encode(array("listadoProductosPedido" => $lista), JSON_PRETTY_PRINT);

      $header = $request->getHeaderLine('Authorization');
      $token = trim(explode("Bearer", $header)[1]);
      EmpleadoController::RegistroLog($token, "Listar platos pedidos");
    }
    else
    {
      $payload = json_encode(array("error" => "No hay pedidos registrados"));
    }
    
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function VerificarProducto($idProducto)
  {
    return Producto::ObtenerUno($idProducto);
  }

  public static function CalcularPrecio($listaIdsProductos)
  {
    $precioTotal = 0;
    foreach($listaIdsProductos as $idProducto)
    {
      $precioTotal += Producto::ObtenerPrecio($idProducto);
    }

    return $precioTotal;
  }

  public static function NuevaLista($idPedido, $listaIdsProductos)
  {
    foreach($listaIdsProductos as $idProducto)
    {
      Producto::CargaProductoPedido($idPedido, $idProducto, 1);
    }
  }

  public static function BuscarProductoPedido($idPlato)
  {
    return Producto::ObtenerProductoPedido($idPlato);
  }

  public static function ModificarListaPedido($idPedido, $listaIdsProductos, $tiemposPrepProductos, $idsEstadosProducto)
  {
    // $listaProductosAnterior = Producto::ObtenerProductosPedido($idPedido);
    // $i = 0;
    // $listaAux = $listaIdsProductos;
    // foreach($listaProductosAnterior as $productoPedidoAnterior)
    // {
    //   $existe = false;
    //   foreach($listaAux as $idProductoPedidoNuevo)
    //   {
    //     if($productoPedidoAnterior["idProducto"] == $idProductoPedidoNuevo)
    //     {
    //       $existe = true;
    //       $index = array_search($idProductoPedidoNuevo, $listaAux);
    //       unset($listaAux[$index]);
    //       break;
    //     }
    //   }

    //   if(!$existe)
    //   {
    //     Producto::BorrarProductoPedido($productoPedidoAnterior["id"]);
    //   }
    // }

    Producto::BorrarProductosPedido($idPedido);

    $i = 0;
    foreach($listaIdsProductos as $idProducto)
    {
      Producto::CargaProductoPedido($idPedido, $idProducto, $tiemposPrepProductos[$i], $idsEstadosProducto[$i]);
      $i++;
    }
  }

  public static function BorrarListaPedido($idPedido)
  {
    Producto::BorrarProductosPedido($idPedido);
  }

  public static function TraerListaPedidoPend($idSector)
  {
    return Producto::ObtenerListaPedidoPend($idSector);
  }

  public static function PrepararProductoPedido($id, $tiempoPreparacion)
  {
    Producto::IniciarPreparacion($id, $tiempoPreparacion);
  }

  public static function TiempoRestanteProducto($id)
  {
    return Producto::ObtenerTiempo($id);
  }

  public static function TiempoRestante($idPedido)
  {
    $listaPedido = Producto::ObtenerProductosPedido($idPedido);
    
    $tiempoMax = 0;
    foreach($listaPedido as $productoPedido)
    {
      if($productoPedido->tiempoPreparacion == "0000-00-00 00:00:00")
      {
        $tiempoMax = "0000-00-00 00:00:00";
        break;
      }

      if($tiempoMax < $productoPedido->tiempoPreparacion)
      {
        $tiempoMax = $productoPedido->tiempoPreparacion;
      }
    }

    return $tiempoMax;
  }
  
  public static function FinalizarProductoPedido($id)
  {
    Producto::FinalizarPreparacion($id);
  }

  public static function RevisarEstadoPlatos($idPedido)
  {
    $mensaje = "Listo";
    $platosPedido = Producto::ObtenerProductosPedido($idPedido);
    foreach($platosPedido as $plato)
    {
      if($plato->idEstadoPedido != 3)
      {
        if($plato->idEstadoPedido == 4)
        {
          $mensaje = "Ya entregado";
          break;
        }
        else if($plato->idEstadoPedido == 5)
        {
          $mensaje = "Cancelado";
          break;
        }
        
        $mensaje = "No listo";
        break;
      }
    }

    return $mensaje;
  }
  
  public static function CambiarEstadoPlatos($idEstado, $idPedido)
  {
    Producto::ActualizarEstadoPlatos($idEstado, $idPedido);
  }
}
