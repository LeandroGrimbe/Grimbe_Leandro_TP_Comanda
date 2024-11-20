<?php

class Pedido
{
    public $id;
    public $idMesa;
    public $precio;
    public $idEstado;

    public function CrearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (id, idMesa, precio, idEstado) VALUES (:id, :idMesa, :precio, :idEstado)");
        $consulta->bindValue(':id', $this->id);
        $consulta->bindValue(':idMesa', $this->idMesa);
        $consulta->bindValue(':precio', $this->precio);
        $consulta->bindValue(':idEstado', $this->idEstado);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function ObtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function ObtenerUno($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE id = :id");
        $consulta->bindValue(':id', $id);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public static function Modificar($id, $idMesa, $precio)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET idMesa = :idMesa, precio = :precio WHERE id = :id");
        $consulta->bindValue(':idMesa', $idMesa);
        $consulta->bindValue(':precio', $precio);
        $consulta->bindValue(':id', $id);
        $consulta->execute();
    }

    public static function Borrar($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET idEstado = 2 WHERE id = :id");
        $consulta->bindValue(':id', $id);
        $consulta->execute();
    }
}