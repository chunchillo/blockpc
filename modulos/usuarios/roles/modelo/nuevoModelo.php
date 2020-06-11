<?php
/* Clase nuevoModelo */
namespace Usuarios\Roles\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class nuevoModelo extends Modelo {
  
    public function __construct() {
        parent::__construct();
    }
    
    public function checkRol($role) {
        $sql = "SELECT * FROM roles WHERE role = :role";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':role', $role, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function nuevo($nuevo) {
        foreach($nuevo as $clave => $valor) {
            $set[] = "{$clave} = :{$clave}";
        }
        $set = implode(", ", $set);
        $sql = "INSERT INTO roles SET {$set};";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute($nuevo);
        return $stmt->rowCount();
    }
}