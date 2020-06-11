<?php
/* Clase indexModelo.php */
namespace Index\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class indexModelo extends Modelo {
  
    public function __construct() {
        parent::__construct();
    }

    public function usuarios() {
		$this->_db->query("SELECT * FROM usuarios;")->fetchAll();
    }
  
}