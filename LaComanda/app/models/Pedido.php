<?php

class Pedido
{
    public $id;
    public $nroMesa;
    public $precio;
    public $idEstadoProceso;

    public function crearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (nroMesa, precio, idEstadoProceso) VALUES (:nroMesa, :precio, :idEstadoProceso)");
        $consulta->bindValue(':nroMesa', $this->nroMesa);
        $consulta->bindValue(':precio', $this->precio);
        $consulta->bindValue(':idEstadoProceso', $this->idEstadoProceso);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedido($pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE id = :pedido");
        $consulta->bindValue(':pedido', $pedido, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    // public static function modificarPedido()
    // {
    //     $objAccesoDato = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET pedido = :pedido, clave = :clave WHERE id = :id");
    //     $consulta->bindValue(':pedido', $, PDO::PARAM_STR);
    //     $consulta->bindValue(':clave', $, PDO::PARAM_STR);
    //     $consulta->bindValue(':id', $id, PDO::PARAM_INT);
    //     $consulta->execute();
    // }

    // public static function borrarPedido($pedido)
    // {
    //     $objAccesoDato = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET fechaBaja = :fechaBaja WHERE id = :id");
    //     $fecha = new DateTime(date("d-m-Y"));
    //     $consulta->bindValue(':id', $pedido, PDO::PARAM_INT);
    //     $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
    //     $consulta->execute();
    // }
}