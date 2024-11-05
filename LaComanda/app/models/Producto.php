<?php

class Producto
{
    public $id;
    public $nombre;
    public $precio;
    public $idCategoria;
    public $idEstado;

    public function crearProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (nombre, precio, idCategoria, idEstado) VALUES (:nombre, :precio, :idCategoria, :idEstado)");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio);
        $consulta->bindValue(':idCategoria', $this->idCategoria, PDO::PARAM_INT);
        $consulta->bindValue(':idEstado', $this->idEstado, PDO::PARAM_INT);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function obtenerProducto($idProducto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productos WHERE id = :idProducto");
        $consulta->bindValue(':idProducto', $idProducto, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }

    public static function modificarProducto($id, $nombre, $precio, $idCategoria)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET nombre = :nombre, precio = :precio, idCategoria = :idCategoria WHERE id = :id");
        $consulta->bindValue(':nombre', $nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $precio);
        $consulta->bindValue(':idCategoria', $idCategoria, PDO::PARAM_INT);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function borrarProducto($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET idEstado = 2 WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function obtenerPrecio($idProducto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT precio FROM productos WHERE id = :id");
        $consulta->bindValue(':id', $idProducto, PDO::PARAM_STR);
        $consulta->execute();

        $resultado = $consulta->fetch();
        return $resultado["precio"];
    }

    public static function CargaProductoPedido($idPedido, $idProducto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO listaProductos (idPedido, idProducto) VALUES (:idPedido, :idProducto)");
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_STR);
        $consulta->bindValue(':idProducto', $idProducto, PDO::PARAM_STR);
        $consulta->execute();
    }
}