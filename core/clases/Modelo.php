<?php 
/* Clase Modelo.php */
namespace Blockpc\Clases;

class Modelo {
	private $_registro;
	protected $_db;
	
	public function __construct() {
		$this->_registro = Registro::getInstancia();
        $this->_db = $this->_registro->get('database');
	}
}