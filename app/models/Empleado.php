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

    // Método para validar si el mozo existe y tiene el rol adecuado
    public static function validarMozoResponsable($mozo_responsable_id)
    {
        $mozo_responsable = Empleado::obtenerPorId($mozo_responsable_id);
        if (!$mozo_responsable) {
            return "ERROR: El mozo responsable no existe.";
        }else if ($mozo_responsable->rol !== 'mozo') {
            return "ERROR: El empleado relacionado no tiene el rol de mozo.";
        }else if ($mozo_responsable->rol == 'mozo' && $mozo_responsable->estado == 'suspendido') {
            return "ERROR: El empleados relacionado está suspendido.";
        }
        return null;  // Sin errores
    }
}