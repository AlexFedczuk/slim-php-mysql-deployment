<?php
require_once './db/AccesoDatos.php';

class Empleado
{
    public $id;
    public $nombre;
    public $rol;
    public $estado;
    public $fecha_ingreso;

    public function crearEmpleado()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO empleados (nombre, rol, estado) VALUES (:nombre, :rol, :estado)");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':rol', $this->rol, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->execute();
        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Empleado');
    }

    public static function cambiarEstado($id, $estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE empleados SET estado = :estado WHERE id = :id");
        $consulta->bindValue(':estado', $estado, PDO::PARAM_STR);
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function obtenerPorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchObject('Empleado');
    }
}