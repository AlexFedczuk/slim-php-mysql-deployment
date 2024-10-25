<?php
class Pedido
{
    public $id;
    public $mesa_id;
    public $cliente_nombre;
    public $productos;
    public $mozo_responsable;
    public $estado;

    // Crear un nuevo pedido en la base de datos
    public function crearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (mesa_id, cliente_nombre, productos, mozo_responsable, estado) VALUES (:mesa_id, :cliente_nombre, :productos, :mozo_responsable, :estado)");
        $consulta->bindValue(':mesa_id', $this->mesa_id, PDO::PARAM_INT);
        $consulta->bindValue(':cliente_nombre', $this->cliente_nombre, PDO::PARAM_STR);
        $consulta->bindValue(':productos', $this->productos, PDO::PARAM_STR);
        $consulta->bindValue(':mozo_responsable', $this->mozo_responsable, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    // Obtener pedido por ID
    public static function obtenerPorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchObject('Pedido');
    }
}