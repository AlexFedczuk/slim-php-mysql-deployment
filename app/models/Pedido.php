<?php
class Pedido {
    public $id;
    public $mesa_id;
    public $cliente_nombre;
    public $productos;
    public $mozo_responsable;
    public $estado;
    public $tiempo_estimado;

    // Crear un nuevo pedido en la base de datos
    public function crearPedido() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "INSERT INTO pedidos (mesa_id, cliente_nombre, productos, mozo_responsable, estado) 
            VALUES (:mesa_id, :cliente_nombre, :productos, :mozo_responsable, :estado)"
        );
        $consulta->bindValue(':mesa_id', $this->mesa_id, PDO::PARAM_INT);
        $consulta->bindValue(':cliente_nombre', $this->cliente_nombre, PDO::PARAM_STR);
        $consulta->bindValue(':productos', $this->productos, PDO::PARAM_STR);
        $consulta->bindValue(':mozo_responsable', $this->mozo_responsable, PDO::PARAM_INT);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId(); // Devuelve el ID generado automáticamente
    }

    // Obtener un pedido por su ID
    public static function obtenerPorId($id) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        $pedido = $consulta->fetchObject('Pedido');

        if ($pedido) {
            // Decodificar JSON de productos para eliminar caracteres de escape
            $pedido->productos = json_decode($pedido->productos);
        }

        return $pedido;
    }

    // Actualizar el estado de un pedido en la base de datos
    public function actualizarEstado() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta(
            "UPDATE pedidos SET estado = :estado WHERE id = :id"
        );
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    // Obtener pedidos por estado
    public static function obtenerPorEstado($estado) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos WHERE estado = :estado");
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    // Obtener todos los pedidos
    public static function obtenerTodos() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }
}