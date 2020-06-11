<?php
/* Clase nuevoModelo */
namespace Usuarios\Permisos\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class nuevoModelo extends Modelo {
  
    public function __construct() {
        parent::__construct();
    }
    
    public function checkPermiso($permiso)
    {
        $sql = "SELECT * FROM permisos WHERE permiso = :permiso";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':permiso', $permiso, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function checkLlave($llave)
    {
        $llave = "{$llave}_acces";
        $sql = "SELECT * FROM permisos WHERE llave = :llave";
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':llave', $llave, \PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function nuevo($permiso)
    {
        foreach($permiso as $clave => $valor) {
            $set[] = "{$clave} = :{$clave}";
        }
        $set = implode(", ", $set);
        $sql = "INSERT INTO permisos SET {$set};";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute($permiso);
        return $stmt->rowCount();
    }
}