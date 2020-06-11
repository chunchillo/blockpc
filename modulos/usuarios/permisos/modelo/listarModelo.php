<?php
/* Clase listarModelo */
namespace Usuarios\Permisos\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class listarModelo extends Modelo {
  
    public function __construct() {
        parent::__construct();
    }

    public function permisos(int $id, string $searchQuery, array $searchArray, string $orderQuery) {
        if ( $id > 1 ) {
            $sql = "SELECT id, permiso, SUBSTRING_INDEX(llave, '_', 1) AS llave, descripcion, editable FROM permisos WHERE id > 1 {$searchQuery} {$orderQuery};";
        } else {
            $sql = "SELECT id, permiso, SUBSTRING_INDEX(llave, '_', 1) AS llave, descripcion, editable FROM permisos WHERE 1 {$searchQuery} {$orderQuery};";
        }
        $stmt = $this->_db->prepare($sql);
        $stmt->execute($searchArray);
        return $stmt->fetchAll();
    }
}