<?php

class Venta
{
    public $id;
    public $nroMesa;
    public $fecha;
    public $idEstado;

    public function RegistrarVenta()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO ventas (nroMesa, fecha, idEstado) VALUES (:nroMesa, :fecha, :idEstado);");
        $consulta->bindValue(':nroMesa', $this->nroMesa, PDO::PARAM_INT);
        $consulta->bindValue(':fecha', $this->fecha);
        $consulta->bindValue(':idEstado', $this->idEstado, PDO::PARAM_INT);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    // public static function obtenerTodos()
    // {
    //     $objAccesoDatos = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados");
    //     $consulta->execute();

    //     return $consulta->fetchAll(PDO::FETCH_CLASS, 'Empleado');
    // }

    // public static function obtenerEmpleado($nombreEmpleado)
    // {
    //     $objAccesoDatos = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados WHERE  empleados.nombre = :nombre;");
    //     $consulta->bindValue(':nombre', $nombreEmpleado, PDO::PARAM_STR);
    //     $consulta->execute();

    //     return $consulta->fetchObject('Empleado');
    // }

    // public static function modificarEmpleado()
    // {
    //     $objAccesoDato = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDato->prepararConsulta("UPDATE empleados SET nombre = :nombre, idRol = :idRol, fechaAlta = :fechaAlta, fechaBaja = :fechaBaja, idEstado = :idEstado WHERE id = :id");
    //     $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
    //     $consulta->bindValue(':idRol', $this->idRol, PDO::PARAM_INT);
    //     $consulta->bindValue(':fechaAlta', $this->fechaAlta, PDO::PARAM_STR);
    //     $consulta->bindValue(':fechaBaja', $this->fechaBaja, PDO::PARAM_STR);
    //     $consulta->bindValue(':idEstado', $this->idEstado, PDO::PARAM_INT);
    //     $consulta->execute();
    // }

    // public static function borrarEmpleado($idEmpleado)
    // {
    //     $objAccesoDato = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDato->prepararConsulta("UPDATE empleados SET fechaBaja = :fechaBaja, idEstado = :idEstado WHERE id = :id");
    //     $fecha = new DateTime(date("Y-m-d"));
    //     $consulta->bindValue(':id', $idEmpleado, PDO::PARAM_INT);
    //     $consulta->bindValue(':fechaBaja', $fecha);
    //     $consulta->bindValue(':idEstado', 2);
    //     $consulta->execute();
    // }
}