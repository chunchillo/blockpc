<?php
/* Clase indexModelo */
namespace Usuarios\Permisos\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class indexModelo extends Modelo {
  
    public function __construct() {
        parent::__construct();
    }

    public function cargarRole($idRole) {
        $sql = "SELECT role FROM roles WHERE id = {$idRole}";
        return $this->_db->query($sql)->fetchColumn();
    }
}