<?php
/* Clase indexModelo */
namespace Usuarios\Acl\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class indexModelo extends Modelo {
  
  public function __construct() {
    parent::__construct();
  }
  
  public function getRoles() {
    return $this->_db->query("SELECT * FROM roles")->fetchAll();
  }
  
  public function getPermisos() {
    return $this->_db->query("SELECT * FROM permisos")->fetchAll();
  }
  
}