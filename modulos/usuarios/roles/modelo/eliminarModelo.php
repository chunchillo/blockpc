<?php
/* Clase eliminarModelo */
namespace Usuarios\Roles\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class eliminarModelo extends Modelo {
  
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
    
    public function eliminar(int $id) {
        $sql = "DELETE FROM roles WHERE id = :id;";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }
}