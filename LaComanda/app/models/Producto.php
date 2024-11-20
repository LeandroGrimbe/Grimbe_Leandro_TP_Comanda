<?php

class Producto
{
    public $id;
    public $nombre;
    public $precio;
    public $idCategoria;
    public $idEstado;

    public function CrearProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO productos (nombre, precio, idCategoria, idEstado) VALUES (:nombre, :precio, :idCategoria, :idEstado)");
        $consulta->bindValue(':nombre', $this->nombre);
        $consulta->bindValue(':precio', $this->precio);
        $consulta->bindValue(':idCategoria', $this->idCategoria);
        $consulta->bindValue(':idEstado', $this->idEstado);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function ObtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function ObtenerUno($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM productos WHERE id = :id");
        $consulta->bindValue(':id', $id);
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }

    public static function Modificar($id, $nombre, $precio, $idCategoria)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET nombre = :nombre, precio = :precio, idCategoria = :idCategoria WHERE id = :id");
        $consulta->bindValue(':nombre', $nombre);
        $consulta->bindValue(':precio', $precio);
        $consulta->bindValue(':idCategoria', $idCategoria);
        $consulta->bindValue(':id', $id);
        $consulta->execute();
    }

    public static function Borrar($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET idEstado = 2 WHERE id = :id");
        $consulta->bindValue(':id', $id);
        $consulta->execute();
    }

    public static function ObtenerPrecio($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT precio FROM productos WHERE id = :id");
        $consulta->bindValue(':id', $id);
        $consulta->execute();

        $resultado = $consulta->fetch();
        return $resultado["precio"];
    }

    public static function CargaProductoPedido($idPedido, $idProducto, $idEstadoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO listaProductosPedido (idPedido, idProducto, idEstadoPedido) VALUES (:idPedido, :idProducto, :idEstadoPedido)");
        $consulta->bindValue(':idPedido', $idPedido);
        $consulta->bindValue(':idProducto', $idProducto);
        $consulta->bindValue(':idEstadoPedido', $idEstadoPedido);
        $consulta->execute();
    }

    public static function ObtenerListadoCompleto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM listaProductosPedido");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS);
    }

    public static function ObtenerProductosPedido($idPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM listaProductosPedido WHERE idPedido = :idPedido");
        $consulta->bindValue(':idPedido', $idPedido);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS);
    }

    public static function BorrarProductosPedido($idPedido)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE listaProductosPedido SET idEstadoPedido = 5 WHERE idPedido = :idPedido");
        $consulta->bindValue(':idPedido', $idPedido);
        $consulta->execute();
    }

    public static function ObtenerListaPedidoPend($idCategoria)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT listaProductosPedido.id, productos.nombre, listaProductosPedido.idPedido FROM listaProductosPedido JOIN productos WHERE productos.id = listaProductosPedido.idProducto AND productos.idCategoria = :idCategoria");
        $consulta->bindValue(':idCategoria', $idCategoria);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS);
    }

    public static function IniciarPreparacion($id, $tiempoPreparacion)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE listaProductosPedido SET tiempoPreparacion = :tiempoPreparacion, idEstadoPedido = 2 WHERE id = :id");
        $consulta->bindValue(':tiempoPreparacion', $tiempoPreparacion);
        $consulta->bindValue(':id', $id);
        $consulta->execute();
    }

    public static function ObtenerTiempo($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT tiempoPreparacion FROM listaProductosPedido WHERE id = :id");
        $consulta->bindValue(':id', $id);
        $consulta->execute();

        $resultado = $consulta->fetch();
        return $resultado["tiempoPreparacion"];
    }
    
    public static function FinalizarPreparacion($id)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE listaProductosPedido SET idEstadoPedido = 3 WHERE id = :id");
        $consulta->bindValue(':id', $id);
        $consulta->execute();
    }

    public static function ObtenerEstadoPedido($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT idEstadoPedido FROM listaProductosPedido WHERE id = :id");
        $consulta->bindValue(':id', $id);
        $consulta->execute();

        $resultado = $consulta->fetch();
        return $resultado["idEstadoPedido"];
    }
}