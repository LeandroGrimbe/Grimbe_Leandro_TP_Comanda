<?php
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';

class MesaController extends Mesa //implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $nombreCliente = $parametros['nombreCliente'];
    $pathFoto = $parametros['pathFoto'];
    $nroMesa = $parametros['nroMesa'];
    $cuenta = $parametros['cuenta'];
    $idEstado = $parametros['idEstado'];

    $mesa = new Mesa();
    $mesa->nombreCliente = $nombreCliente;
    $mesa->pathFoto = $pathFoto;
    $mesa->nroMesa = $nroMesa;
    $mesa->cuenta = $cuenta;
    $mesa->idEstadoMesa = $idEstadoMesa;
    $mesa->OcuparMesa();

    $payload = json_encode(array("mensaje" => "Mesa cargada con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    $nroMesa = $args['nroMesa'];
    $mesa = Mesa::obtenerMesa($nroMesa);
    $payload = json_encode($mesa);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Mesa::obtenerTodas();
    $payload = json_encode(array("listaMesas" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
      $parametros = $request->getParsedBody();

      $id = $parametros['id'];
      $nombreCliente = $parametros['nombreCliente'];
      $pathFoto = $parametros['pathFoto'];
      $nroMesa = $parametros['nroMesa'];
      $cuenta = $parametros['cuenta'];

      Mesa::modificarMesa($id, $nombreCliente, $pathFoto, $nroMesa, $cuenta);

      $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
      $parametros = $request->getParsedBody();

      $idMesa = $parametros['id'];
      Mesa::borrarMesa($idMesa);

      $payload = json_encode(array("mensaje" => "Mesa borrada con exito"));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
  }

  public static function ObtenerMesaLibre()
  {
    $nroMesa = -1;
    do
    {
      $nroMesa = rand(1,99999);
    }
    while(Mesa::VerificarMesa($nroMesa));

    return $nroMesa;
  }

  public static function NuevaMesa($nombreCliente, $pathFoto, $nroMesa)
  {
    $mesa = new Mesa();
    $mesa->nombreCliente = $nombreCliente;
    $mesa->pathFoto = $pathFoto;
    $mesa->nroMesa = $nroMesa;
    $mesa->cuenta = 0;
    $mesa->idEstadoMesa = 2;
    $mesa->OcuparMesa();
  }

  public static function BuscarMesa($nroMesa)
  {
    return Mesa::VerificarMesa($nroMesa);
  }
}
