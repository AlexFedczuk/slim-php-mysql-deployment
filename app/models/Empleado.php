<?php
require_once './db/AccesoDatos.php';

class Empleado
{
    public $id;
    public $nombre;
    public $clave;
    public $rol;
    public $estado;

    // Método para guardar un empleado en la base de datos
    public function guardar()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO empleados (nombre, clave, rol, estado) VALUES (:nombre, :clave, :rol, :estado)");
        
        // Hash de la clave antes de guardarla
        $claveHash = password_hash($this->clave, PASSWORD_DEFAULT);
        
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $claveHash, PDO::PARAM_STR);
        $consulta->bindValue(':rol', $this->rol, PDO::PARAM_STR);
        $consulta->bindValue(':estado', 'activo', PDO::PARAM_STR); // Estado inicial por defecto
        $consulta->execute();
        
        return $objAccesoDatos->obtenerUltimoId();
    }

    // Método para cambiar el estado del empleado
    public function cambiarEstado($nuevoEstado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE empleados SET estado = :estado WHERE id = :id");
        $consulta->bindValue(':estado', strtolower($nuevoEstado), PDO::PARAM_STR);
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
    }

    // Obtener todos los empleados
    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Empleado');
    }

    // Obtener un empleado por ID
    public static function obtenerPorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM empleados WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchObject('Empleado');
    }

    // Validar si el rol es permitido
    public static function esRolValido($rol)
    {
        $json_data = file_get_contents('./data/roles.json');
        $roles_data = json_decode($json_data, true);
        return in_array(strtolower($rol), $roles_data['roles']);
    }

    // Validar si el estado es permitido
    public static function esEstadoValido($estado)
    {
        $json_data = file_get_contents('./data/estados_de_empleados.json');
        $estados_data = json_decode($json_data, true);
        return in_array(strtolower($estado), $estados_data['estados']);
    }

    // Método para validar el rol y estado de un mozo responsable
    public static function validarMozoResponsable($mozo_responsable_id)
    {
        $mozo_responsable = Empleado::obtenerPorId($mozo_responsable_id);
        if (!$mozo_responsable) {
            return "ERROR: El mozo responsable no existe.";
        } elseif ($mozo_responsable->rol !== 'mozo') {
            return "ERROR: El empleado relacionado no tiene el rol de mozo.";
        } elseif ($mozo_responsable->estado === 'suspendido') {
            return "ERROR: El empleado está suspendido.";
        }
        return null;  // Sin errores
    }

    public static function borrarEmpleado($id)
    {
        try {
            $objAccesoDatos = AccesoDatos::obtenerInstancia();
            $consulta = $objAccesoDatos->prepararConsulta("DELETE FROM empleados WHERE id = :id");
            $consulta->bindValue(':id', $id, PDO::PARAM_INT);
            $consulta->execute();
        } catch (PDOException $e) {
            // Manejar el error de manera adecuada, tal vez lanzando una excepción personalizada
            throw new Exception("Error al borrar el empleado: " . $e->getMessage());
        }
    }
}