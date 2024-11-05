<?php

class Mesa
{
    public $id;
    public $nombreCliente;
    public $pathFoto;
    public $nroMesa;
    public $cuenta;
    public $idEstadoMesa;

    public function OcuparMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (nombreCliente, pathFoto, nroMesa, cuenta, idEstadoMesa) VALUES (:nombreCliente, :pathFoto, :nroMesa, :cuenta, :idEstadoMesa)");
        $consulta->bindValue(':nombreCliente', $this->nombreCliente);
        $consulta->bindValue(':pathFoto', $this->pathFoto);
        $consulta->bindValue(':nroMesa', $this->nroMesa);
        $consulta->bindValue(':cuenta', $this->cuenta);
        $consulta->bindValue(':idEstadoMesa', $this->idEstadoMesa);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodas()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function obtenerMesa($nroMesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas WHERE nroMesa = :nroMesa");
        $consulta->bindValue(':nroMesa', $nroMesa, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Mesa');
    }

    public static function modificarMesa($id, $nombreCliente, $pathFoto, $nroMesa, $cuenta)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET nombreCliente = :nombreCliente, pathFoto = :pathFoto, nroMesa = :nroMesa, cuenta = :cuenta WHERE id = :id");
        $consulta->bindValue(':nombreCliente', $nombreCliente);
        $consulta->bindValue(':pathFoto', $pathFoto);
        $consulta->bindValue(':nroMesa', $nroMesa);
        $consulta->bindValue(':cuenta', $cuenta);
        $consulta->bindValue(':id', $id);
        $consulta->execute();
    }

    public static function borrarMesa($idMesa)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET idEstadoMesa = 1 WHERE id = :id");
        $consulta->bindValue(':id', $idMesa, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function VerificarMesa($nroMesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas WHERE nroMesa = :a AND idEstadoMesa = 2 OR nroMesa = :b AND idEstadoMesa = 3 OR nroMesa = :c AND idEstadoMesa = 4;");
        $consulta->bindValue(':a', $nroMesa);
        $consulta->bindValue(':b', $nroMesa);
        $consulta->bindValue(':c', $nroMesa);
        $consulta->execute();

        $coincidencias = $consulta->fetchAll();
        $retorno = false;
        if($coincidencias != null)
        {
            $retorno = true;
        }

        return $retorno;
    }
}