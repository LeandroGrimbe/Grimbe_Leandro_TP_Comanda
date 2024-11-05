<?php
require_once './models/Venta.php';
require_once './controllers/MesaController.php';

require_once './interfaces/IApiUsable.php';

class Ventacontroller extends Venta //implements IApiUsable
{
    public function NuevaVenta($request, $response, $args)
    {
      $parametros = $request->getParsedBody();

      $nombreCliente = $parametros['nombreCliente'];
      $pathFoto = $parametros['pathFoto'];
      $nroMesa = MesaController::ObtenerMesaLibre();
      $fecha = date("Y-m-d");
      $idEstado = 1;
      
      $venta = new Venta();
      $venta->nroMesa = $nroMesa;
      $venta->fecha = $fecha;
      $venta->idEstado = $idEstado;
      $venta->RegistrarVenta();

      MesaController::NuevaMesa($nombreCliente, $pathFoto, $nroMesa);
      
      $payload = json_encode(array("mensaje" => "Cliente asignado correctamente a una mesa. Esperando pedidos"));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
}
