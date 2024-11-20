<?php
require_once './models/Empleado.php';
require_once './utils/AutentificadorJWT.php';

class Empleadocontroller extends Empleado
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $usuario = $parametros['usuario'];
    $clave = $parametros['clave'];
    $idRol = $parametros['idRol'];
    $fechaAlta = date("Y-m-d");
    $fechaBaja = "";
    $idEstado = 1;
    
    $emp = new Empleado();
    $emp->usuario = $usuario;
    $emp->clave = $clave;
    $emp->idRol = $idRol;
    $emp->fechaAlta = $fechaAlta;
    $emp->fechaBaja = $fechaBaja;
    $emp->idEstado = $idEstado;
    $emp->CrearEmpleado();

    $payload = json_encode(array("mensaje" => "Empleado creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  // public function TraerEmpleado($request, $response, $args)
  // {
  //   $id = $args['id'];
  //   $empleado = Empleado::ObtenerUno($id);
  //   if($empleado)
  //   {
  //     $payload = json_encode($empleado);
  //   }
  //   else
  //   {
  //     $payload = json_encode(array("error" => "Empleado no encontrado"));
  //   }

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }

  // public function TraerTodos($request, $response, $args)
  // {
  //   $lista = Empleado::ObtenerTodos();
  //   if($lista)
  //   {
  //     $payload = json_encode(array("listaEmpleados" => $lista));
  //   }
  //   else
  //   {
  //     $payload = json_encode(array("error" => "No hay empleados registrados"));
  //   }

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }

  // public function ModificarEmpleado($request, $response, $args)
  // {
  //   $parametros = $request->getParsedBody();

  //   $id = $parametros['id'];
  //   $usuario = $parametros['usuario'];
  //   $clave = $parametros['clave'];
  //   $idRol = $parametros['idRol'];
  //   $fechaAlta = $parametros['fechaAlta'];

  //   if(Empleado::ObtenerUno($id)) 
  //   {
  //     Empleado::Modificar($id, $usuario, $clave, $idRol, $fechaAlta);
  //     $payload = json_encode(array("mensaje" => "Empleado modificado con exito"));
  //   } 
  //   else
  //   {
  //     $payload = json_encode(array("error" => "No se encontro el id, no se realizaron cambios"));
  //   }

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }

  // public function BorrarEmpleado($request, $response, $args)
  // {
  //   $parametros = $request->getParsedBody();

  //   $id = $parametros['id'];

  //   if(Empleado::ObtenerUno($id)) 
  //   {
  //     Empleado::Borrar($id);
  //     $payload = json_encode(array("mensaje" => "Empleado borrado con exito"));
  //   } 
  //   else
  //   {
  //     $payload = json_encode(array("error" => "No se encontro el id, no se realizaron cambios"));
  //   }

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // }

  public function LogueoEmpleado($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $usuario = $parametros['usuario'];
    $clave = $parametros['clave'];

    $empleado = Empleado::IniciarSesion($usuario, $clave);
    if($empleado)
    {
      $datos = array('usuario' => $usuario, 'idRol' => $empleado->idRol);

      $token = AutentificadorJWT::CrearToken($datos);
      $payload = json_encode(array('jwt' => $token));
    } 
    else 
    {
      $payload = json_encode(array('error' => 'Usuario o clave incorrectos, o cuenta inhabilitada, reintente..'));
    }
    
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}
