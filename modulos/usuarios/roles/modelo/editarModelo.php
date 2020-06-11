<?php
/* Clase editarModelo */
namespace Usuarios\Roles\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class editarModelo extends Modelo {
  
    public function __construct() {
        parent::__construct();
    }
    
    public function role(int $id) {
        $sql = "SELECT * FROM roles WHERE id = :id;";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function checkRol(string $role, int $id) {
        $sql = "SELECT * FROM roles WHERE role = :role AND id != :id";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':role', $role, \PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function editar(array $rol, int $id) {
        foreach($rol as $clave => $valor) {
            $set[] = "{$clave} = :{$clave}";
        }
        $set = implode(", ", $set);
        $rol['id'] = $id;
        $sql = "UPDATE roles SET {$set} WHERE id = :id;";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute($rol);
        return $stmt->rowCount();
    }
}