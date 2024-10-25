<?php
require_once './db/AccesoDatos.php';

class Mesa
{
    public $id;
    public $estado;
    public $mozo_responsable;

    public function crearMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO mesas (id, estado, mozo_responsable) VALUES (:id, :estado, :mozo_responsable)");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':mozo_responsable', $this->mozo_responsable, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function obtenerTodas()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public static function cambiarEstado($id, $estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE mesas SET estado = :estado WHERE id = :id");
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function obtenerPorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM mesas WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchObject('Mesa');
    }

    // Método para validar si el mozo existe y tiene el rol adecuado
    public static function validarMesa($mesa_id)
    {
        $mesa = Mesa::obtenerPorId($mesa_id);
        if (!$mesa) {
            return "ERROR: La mesa no existe.";
        }
        /*if ($mesa->rol !== 'mozo') {
            return "ERROR: El empleado relacionado no tiene el rol de mozo.";
        }
        if ($mesa->estado == 'suspendido') {
            return "ERROR: El empleado relacionado está suspendido.";
        }*/
        return null;  // Sin errores
    }
}