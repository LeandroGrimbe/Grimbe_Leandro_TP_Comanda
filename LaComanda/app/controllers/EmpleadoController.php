<?php
require_once './models/Empleado.php';
require_once './utils/AutentificadorJWT.php';
require_once './utils/MYPDF.php';

class Empleadocontroller extends Empleado
{
  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $usuario = $parametros['usuario'];
    $clave = md5($parametros['clave']);
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

  public function TraerTodos($request, $response, $args)
  {
    $lista = Empleado::ObtenerTodos();
    if($lista)
    {
      $payload = json_encode(array("listaEmpleados" => $lista), JSON_PRETTY_PRINT);

      $header = $request->getHeaderLine('Authorization');
      $token = trim(explode("Bearer", $header)[1]);
      self::RegistroLog($token, "Listar Empleados");
    }
    else
    {
      $payload = json_encode(array("error" => "No hay empleados registrados"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function LogueoEmpleado($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $usuario = $parametros['usuario'];
    $clave = md5($parametros['clave']);

    $empleado = Empleado::IniciarSesion($usuario, $clave);
    if($empleado)
    {
      $datos = array('usuario' => $usuario, 'idRol' => $empleado->idRol);

      $token = AutentificadorJWT::CrearToken($datos);
      $payload = json_encode(array('jwt' => $token), JSON_PRETTY_PRINT);

      self::RegistroLog($token, "Logueo Exitoso");
    } 
    else 
    {
      $payload = json_encode(array('error' => 'Usuario o clave incorrectos, o cuenta inhabilitada, reintente..'));
    }
    
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ListadoPDF($request, $response, $args)
  {
    $listaEmpleados = Empleado::ObtenerTodos();
    
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    $pdf->SetMargins(25, 35, 25);
    $pdf->SetHeaderMargin(20);
    $pdf->SetAutoPageBreak(false);

    $pdf->AddPage();

    $pdf->SetFillColor(0, 0, 0, 100);
    $pdf->SetTextColor(0, 0, 0, 100);
    $pdf->SetXY(150, 15);
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Write(0, 'Fecha: '. date("Y-m-d"));

    $pdf->Ln(20);

    $pdf->SetFont('times', 'BI', 12);
    $pdf->Cell(0, 10, 'Listado de Empleados', 0, 1, 'C');


    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(10, 10, "ID", 1, 0, 'C');
    $pdf->Cell(30, 10, "USUARIO", 1, 0, 'C');
    $pdf->Cell(40, 10, "ROL", 1, 0, 'C');
    $pdf->Cell(37, 10, "FECHA INGRESO", 1, 0, 'C');
    $pdf->Cell(37, 10, "FECHA BAJA", 1, 0, 'C');
    $pdf->Ln();
    foreach ($listaEmpleados as $empleado) 
    {
      $rol = "";
      switch($empleado->idRol)
      {
        case 1:
          $rol = "bartender";
          break;
        
        case 2:
          $rol = "cervecero";
          break;

        case 3:
          $rol = "cocinero";
          break;

        case 4:
          $rol = "mozo";
          break;

        case 5:
          $rol = "socio";
          break;
      }

      $pdf->Cell(10, 10, $empleado->id, 1, 0, 'C');
      $pdf->Cell(30, 10, $empleado->usuario, 1, 0, 'C');
      $pdf->Cell(40, 10, $rol, 1, 0, 'C');
      $pdf->Cell(37, 10, $empleado->fechaAlta, 1, 0, 'C');
      $pdf->Cell(37, 10, $empleado->fechaBaja, 1, 0, 'C');
      $pdf->Ln();
    }

    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);
    self::RegistroLog($token, "Descargar PDF");

    $pdfContent = $pdf->Output('empleados.pdf', 'S');
    $response->getBody()->write($pdfContent);

    return $response->withHeader('Content-Type', 'application/pdf')
                    ->withHeader('Content-Disposition', 'inline; filename="empleados.pdf"');
  }

  public static function RegistroLog($token, $tarea)
  {
    $pathCarpetalog = "./log/";
    if (!file_exists($pathCarpetalog)) 
    {
        mkdir($pathCarpetalog, 0777, true);
    }

    $dataEmpleado = AutentificadorJWT::ObtenerData($token);

    $fecha = date("Y-m-d H:i:s");
    $datosLog = $fecha . "\t\t" . $dataEmpleado->usuario . "\t\t\t" . $tarea . "\n";

    $pathLog = $pathCarpetalog . "log.txt";
    $archivo = fopen($pathLog, "a");
    $escritura = fwrite($archivo, $datosLog);
    fclose($archivo);
  }

  public function BajaEmpleado($request, $response, $args)
  {
      $parametros = $request->getParsedBody();

      $empleadoId = $parametros['id'];
      Empleado::Borrar($empleadoId);

      $header = $request->getHeaderLine('Authorization');
      $token = trim(explode("Bearer", $header)[1]);
      self::RegistroLog($token, "Inhabilitar Empleado nro " . $empleadoId);

      $payload = json_encode(array("mensaje" => "Empleado inhabilitado con exito"));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
  }
}
