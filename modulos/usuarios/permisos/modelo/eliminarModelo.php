<?php
/* Clase eliminarModelo */
namespace Usuarios\Permisos\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class eliminarModelo extends Modelo {
  
  public function __construct() {
    parent::__construct();
  }
  
  public function cargarPermiso(int $id)
  {
    $sql = "SELECT id, permiso, SUBSTRING_INDEX(llave, '_', 1) AS llave, descripcion, editable FROM permisos WHERE id = :id;";
    $stmt = $this->_db->prepare($sql);
    $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch();
  }
  
  public function eliminar(int $id)
  {
    $sql = "DELETE FROM permisos WHERE id = :id;";
    $stmt = $this->_db->prepare($sql);
    $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->rowCount();
  }
}