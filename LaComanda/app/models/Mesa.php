<?php

class Mesa
{
    public $id;
    public $nombreCliente;
    public $pathFoto;
    public $nroMesa;
    public $cuenta;
    public $fecha;
    public $idEstadoMesa;

    public function OcuparMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (nombreCliente, pathFoto, nroMesa, cuenta, fecha, idEstadoMesa) VALUES (:nombreCliente, :pathFoto, :nroMesa, :cuenta, :fecha, :idEstadoMesa)");
        $consulta->bindValue(':nombreCliente', $this->nombreCliente);
        $consulta->bindValue(':pathFoto', $this->pathFoto);
        $consulta->bindValue(':nroMesa', $this->nroMesa);
        $consulta->bindValue(':cuenta', $this->cuenta);
        $consulta->bindValue(':fecha', $this->fecha);
        $consulta->bindValue(':idEstadoMesa', $this->idEstadoMesa);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function ObtenerTodas()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function ObtenerUna($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas WHERE id = :id");
        $consulta->bindValue(':id', $id);
        $consulta->execute();

        return $consulta->fetchObject('Mesa');
    }

    public static function Modificar($id, $nombreCliente, $pathFoto, $nroMesa, $cuenta, $fecha, $idEstadoMesa)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET nombreCliente = :nombreCliente, pathFoto = :pathFoto, nroMesa = :nroMesa, cuenta = :cuenta, fecha = :fecha, idEstadoMesa = :idEstadoMesa WHERE id = :id");
        $consulta->bindValue(':nombreCliente', $nombreCliente);
        $consulta->bindValue(':pathFoto', $pathFoto);
        $consulta->bindValue(':nroMesa', $nroMesa);
        $consulta->bindValue(':cuenta', $cuenta);
        $consulta->bindValue(':fecha', $fecha);
        $consulta->bindValue(':idEstadoMesa', $idEstadoMesa);
        $consulta->bindValue(':id', $id);
        $consulta->execute();
    }

    public static function Borrar($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET idEstadoMesa = 5 WHERE id = :id");
        $consulta->bindValue(':id', $id);
        $consulta->execute();
    }

    public static function ObtenerMesaPorNro($nroMesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas WHERE nroMesa = :a AND idEstadoMesa = 1 OR nroMesa = :b AND idEstadoMesa = 2 OR nroMesa = :c AND idEstadoMesa = 3;");
        $consulta->bindValue(':a', $nroMesa);
        $consulta->bindValue(':b', $nroMesa);
        $consulta->bindValue(':c', $nroMesa);
        $consulta->execute();

        $coincidencia = $consulta->fetchObject('Mesa');

        return $coincidencia;
    }

    public static function ActualizarCuenta($nroMesa, $nuevaCuenta)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET cuenta = :nuevaCuenta WHERE nroMesa = :a AND idEstadoMesa = 1 OR nroMesa = :b AND idEstadoMesa = 2 OR nroMesa = :c AND idEstadoMesa = 3;");
        $consulta->bindValue(':nuevaCuenta', $nuevaCuenta);
        $consulta->bindValue(':a', $nroMesa);
        $consulta->bindValue(':b', $nroMesa);
        $consulta->bindValue(':c', $nroMesa);
        $consulta->execute();
    }

    public static function ActualizarEstado($idEstadoMesa, $nroMesa)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET idEstadoMesa = :idEstadoMesa WHERE nroMesa = :nroMesa");
        $consulta->bindValue(':idEstadoMesa', $idEstadoMesa);
        $consulta->bindValue(':nroMesa', $nroMesa);
        $consulta->execute();
    }
}