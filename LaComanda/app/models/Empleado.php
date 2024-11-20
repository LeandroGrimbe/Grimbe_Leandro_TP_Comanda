<?php

class Empleado
{
    public $id;
    public $usuario;
    public $clave;
    public $idRol;
    public $fechaAlta;
    public $fechaBaja;
    public $idEstado;

    public function CrearEmpleado()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO empleados (usuario, clave, idRol, fechaAlta, fechaBaja, idEstado) VALUES (:usuario, :clave, :idRol, :fechaAlta, :fechaBaja, :idEstado);");
        $consulta->bindValue(':usuario', $this->usuario);
        $consulta->bindValue(':clave', $this->clave);
        $consulta->bindValue(':idRol', $this->idRol, PDO::PARAM_INT);
        $consulta->bindValue(':fechaAlta', $this->fechaAlta);
        $consulta->bindValue(':fechaBaja', $this->fechaBaja);
        $consulta->bindValue(':idEstado', $this->idEstado, PDO::PARAM_INT);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function ObtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Empleado');
    }

    public static function ObtenerUno($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados WHERE id = :id;");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Empleado');
    }

    public static function Modificar($id, $usuario, $clave, $idRol, $fechaAlta)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE empleados SET usuario = :usuario, clave = :clave, idRol = :idRol, fechaAlta = :fechaAlta WHERE id = :id");
        $consulta->bindValue(':usuario', $usuario);
        $consulta->bindValue(':clave', $clave);
        $consulta->bindValue(':idRol', $idRol, PDO::PARAM_INT);
        $consulta->bindValue(':fechaAlta', $fechaAlta);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function Borrar($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE empleados SET fechaBaja = :fechaBaja, idEstado = 2 WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', date("Y-m-d"));
        $consulta->execute();
    }

    public static function IniciarSesion($usuario, $clave)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados WHERE  usuario = :usuario AND clave = :clave;");
        $consulta->bindValue(':usuario', $usuario);
        $consulta->bindValue(':clave', $clave);
        $consulta->execute();

        return $consulta->fetchObject('Empleado');
    }
}