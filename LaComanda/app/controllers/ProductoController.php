<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductoController extends Producto implements IApiUsable
{
    public function CargarUno($request, $response, $args)
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
      $prod->crearProducto();

      $payload = json_encode(array("mensaje" => "Producto creado con exito"));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
      $prod = $args['idProducto'];
      $producto = Producto::obtenerProducto($prod);
      $payload = json_encode($producto);

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
      $lista = Producto::obtenerTodos();
      $payload = json_encode(array("listaProductos" => $lista));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
      $parametros = $request->getParsedBody();

      $id = $parametros['id'];
      $nombre = $parametros['nombre'];
      $precio = $parametros['precio'];
      $idCategoria = $parametros['idCategoria'];

      Producto::modificarProducto($id, $nombre, $precio, $idCategoria);

      $payload = json_encode(array("mensaje" => "Producto modificado con exito"));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
      $parametros = $request->getParsedBody();

      $id = $parametros['idProducto'];
      Producto::borrarProducto($id);

      $payload = json_encode(array("mensaje" => "Producto borrado con exito"));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public static function CalcularPrecio($listaIdsProductos)
    {
      $precioTotal = 0;
      foreach($listaIdsProductos as $idProducto)
      {
        $precioTotal += Producto::obtenerPrecio($idProducto);
      }

      return $precioTotal;
    }

    public static function NuevaLista($idPedido, $listaIdsProductos)
    {
      foreach($listaIdsProductos as $idProducto)
      {
        Producto::CargaProductoPedido($idPedido, $idProducto);
      }
    }
}
