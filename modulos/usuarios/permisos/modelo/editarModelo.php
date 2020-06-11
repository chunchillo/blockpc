<?php
/* Clase editarModelo */
namespace Usuarios\Permisos\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class editarModelo extends Modelo {
  
    public function __construct() {
        parent::__construct();
    }
    
    public function cargarPermiso($id)
    {
        $sql = "SELECT id, permiso, SUBSTRING_INDEX(llave, '_', 1) AS llave, descripcion, editable FROM permisos WHERE id = :id;";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function checkPermiso(string $permiso, int $id)
    {
        $sql = "SELECT * FROM permisos WHERE permiso = :permiso AND id != :id;";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':permiso', $permiso, \PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function checkLlave(string $llave, int $id)
    {
        $llave = "{$llave}_acces";
        $sql = "SELECT * FROM permisos WHERE llave = :llave AND id != :id;";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':llave', $llave, \PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function editar(array $permiso, int $id)
    {
        foreach($permiso as $clave => $valor) {
            $set[] = "{$clave} = :{$clave}";
        }
        $set = implode(", ", $set);
        $permiso['id'] = $id;
        $sql = "UPDATE permisos SET {$set} WHERE id = :id;";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute($permiso);
        return $stmt->rowCount();
    }
}