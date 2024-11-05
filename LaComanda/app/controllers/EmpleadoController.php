<?php
require_once './models/Empleado.php';
require_once './interfaces/IApiUsable.php';

class Empleadocontroller extends Empleado implements IApiUsable
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $nombre = $parametros['nombre'];
    $idRol = $parametros['idRol'];
    $fechaAlta = date("Y-m-d");
    $fechaBaja = "";
    $idEstado = 1;
    
    $emp = new Empleado();
    $emp->nombre = $nombre;
    $emp->idRol = $idRol;
    $emp->fechaAlta = $fechaAlta;
    $emp->fechaBaja = $fechaBaja;
    $emp->idEstado = $idEstado;
    $emp->crearEmpleado();

    $payload = json_encode(array("mensaje" => "Empleado creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    $nombre = $args['nombreEmpleado'];
    $empleado = Empleado::obtenerEmpleado($nombre);
    $payload = json_encode($empleado);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Empleado::obtenerTodos();
    $payload = json_encode(array("listaEmpleados" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
      $parametros = $request->getParsedBody();

      $id = $parametros['id'];
      $nombre = $parametros['nombre'];
      $idRol = $parametros['idRol'];
      $fechaAlta = $parametros['fechaAlta'];

      Empleado::modificarEmpleado($id, $nombre, $idRol, $fechaAlta);

      $payload = json_encode(array("mensaje" => "Empleado modificado con exito"));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
      $parametros = $request->getParsedBody();

      $empleadoId = $parametros['id'];
      Empleado::borrarEmpleado($empleadoId);

      $payload = json_encode(array("mensaje" => "Empleado borrado con exito"));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
  }
}
