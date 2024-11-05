<?php
require_once './models/Pedido.php';
require_once './controllers/ProductoController.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido //implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nroMesa = $parametros['nroMesa'];
        $idEstadoProceso = 2;

        $listaIdsProductos = $parametros['listaIdsProductos'];
        $precio = ProductoController::CalcularPrecio($listaIdsProductos);

        $pedido = new Pedido();
        $pedido->nroMesa = $nroMesa;
        $pedido->precio = $precio;
        $pedido->idEstadoProceso = $idEstadoProceso;

        $idPedido = $pedido->crearPedido();
        ProductoController::NuevaLista($idPedido, $listaIdsProductos);

        $payload = json_encode(array("mensaje" => "Pedido creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $idPedido = $args['idPedido'];
        $pedido = Pedido::obtenerPedido($idPedido);
        $payload = json_encode($pedido);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::obtenerTodos();
        $payload = json_encode(array("listaPedidos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    // public function ModificarUno($request, $response, $args)
    // {
    //     $parametros = $request->getParsedBody();

    //     $nombre = $parametros['nombre'];
    //     Pedido::modificarPedido($nombre);

    //     $payload = json_encode(array("mensaje" => "Pedido modificado con exito"));

    //     $response->getBody()->write($payload);
    //     return $response
    //       ->withHeader('Content-Type', 'application/json');
    // }

    // public function BorrarUno($request, $response, $args)
    // {
    //     $parametros = $request->getParsedBody();

    //     $pedidoId = $parametros['pedidoId'];
    //     Pedido::borrarPedido($pedidoId);

    //     $payload = json_encode(array("mensaje" => "Pedido borrado con exito"));

    //     $response->getBody()->write($payload);
    //     return $response
    //       ->withHeader('Content-Type', 'application/json');
    // }
}
