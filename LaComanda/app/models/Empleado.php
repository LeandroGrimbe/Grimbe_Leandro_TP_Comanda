<?php

class Empleado
{
    public $id;
    public $nombre;
    public $idRol;
    public $fechaAlta;
    public $fechaBaja;
    public $idEstado;

    public function crearEmpleado()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO empleados (nombre, idRol, fechaAlta, fechaBaja, idEstado) VALUES (:nombre, :idRol, :fechaAlta, :fechaBaja, :idEstado);");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':idRol', $this->idRol, PDO::PARAM_INT);
        $consulta->bindValue(':fechaAlta', $this->fechaAlta);
        $consulta->bindValue(':fechaBaja', $this->fechaBaja);
        $consulta->bindValue(':idEstado', $this->idEstado, PDO::PARAM_INT);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Empleado');
    }

    public static function obtenerEmpleado($nombreEmpleado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados WHERE  empleados.nombre = :nombre;");
        $consulta->bindValue(':nombre', $nombreEmpleado, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Empleado');
    }

    public static function modificarEmpleado($id, $nombre, $idRol, $fechaAlta)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE empleados SET nombre = :nombre, idRol = :idRol, fechaAlta = :fechaAlta WHERE id = :id");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':idRol', $idRol, PDO::PARAM_INT);
        $consulta->bindValue(':fechaAlta', $fechaAlta, PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarEmpleado($idEmpleado)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE empleados SET fechaBaja = :fechaBaja, idEstado = 2 WHERE id = :id");
        $fecha = date("Y-m-d");
        $consulta->bindValue(':id', $idEmpleado, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', $fecha);
        $consulta->execute();
    }
}