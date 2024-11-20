<?php
require_once './models/Producto.php';

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

  // public function TraerProducto($request, $response, $args)
  // {
  //   $id = $args['id'];
  //   $producto = Producto::ObtenerUno($id);
  //   if($producto)
  //   {
  //     $payload = json_encode($producto);
  //   }
  //   else
  //   {
  //     $payload = json_encode(array("error" => "Producto no encontrado"));
  //   }

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }

  // public function TraerTodos($request, $response, $args)
  // {
  //   $lista = Producto::ObtenerTodos();
  //   if($lista)
  //   {
  //     $payload = json_encode(array("listaProductos" => $lista));
  //   }
  //   else
  //   {
  //     $payload = json_encode(array("error" => "No hay productos registrados"));
  //   }

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }
  
  // public function ModificarProducto($request, $response, $args)
  // {
  //   $parametros = $request->getParsedBody();

  //   $id = $parametros['id'];
  //   $nombre = $parametros['nombre'];
  //   $precio = $parametros['precio'];
  //   $idCategoria = $parametros['idCategoria'];

  //   if(Producto::ObtenerUno($id)) 
  //   {
  //     Producto::Modificar($id, $nombre, $precio, $idCategoria);
  //     $payload = json_encode(array("mensaje" => "Producto modificado con exito"));
  //   } 
  //   else
  //   {
  //     $payload = json_encode(array("error" => "No se encontro el id, no se realizaron cambios"));
  //   }

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }

  // public function BorrarProducto($request, $response, $args)
  // {
  //   $parametros = $request->getParsedBody();

  //   $id = $parametros['id'];

  //   if(Producto::ObtenerUno($id)) 
  //   {
  //     Producto::Borrar($id);
  //     $payload = json_encode(array("mensaje" => "Producto borrado con exito"));
  //   } 
  //   else
  //   {
  //     $payload = json_encode(array("error" => "No se encontro el id, no se realizaron cambios"));
  //   }

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }

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

  public static function TraerListaCsv($request, $response, $args)
  {
    $listaProductos = Producto::ObtenerTodos();

    $archivo = fopen("./ListadosCsv/productosExistentes.csv", "a");
    foreach($listaProductos as $producto)
    {
      $datosAGuardar = "\n" . $producto->nombre . "," . $producto->precio . "," . $producto->idCategoria . "," . $producto->idEstado;
    }

    $datosAGuardar .= "\n";

    $escritura = fwrite($archivo, $datosAGuardar);
    
    fclose($archivo);

    if($escritura > 0)
    {
      $payload = json_encode(array("mensaje" => "Lista de productos descargada con exito"));
    }
    else
    {
      $payload = json_encode(array("mensaje" => "Hubo errores al guardar los datos, no se realizaron cambios"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
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
    }
    else
    {
      $payload = json_encode(array("error" => "No existe la lista a cargar, no se realizaron cambios"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function NuevaLista($idPedido, $listaIdsProductos)
  {
    foreach($listaIdsProductos as $idProducto)
    {
      Producto::CargaProductoPedido($idPedido, $idProducto, 1);
    }
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
      if($tiempoMax < $productoPedido->tiempoPreparacion)
      {
        $tiempoMax = $productoPedido->tiempoPreparacion;
      }
    }

    return $tiempoMax;
  }
  
  public static function ListadoCompleto($request, $response, $args)
  {
    $lista = Producto::ObtenerListadoCompleto();
    if($lista)
    {
      $payload = json_encode(array("listaPedidos" => $lista));
    }
    else
    {
      $payload = json_encode(array("error" => "No hay pedidos registrados"));
    }
    
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
  
  public static function FinalizarProductoPedido($id)
  {
    Producto::FinalizarPreparacion($id);
  }

  public static function RevisarEstado($id)
  {
    $estado = "no listo";
    if(Producto::ObtenerEstadoPedido($id) == 3)
    {
      $estado = "listo";
    }
    
    return $estado;
  }
}
