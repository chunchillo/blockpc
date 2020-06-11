<?php
/* Clase dashboardModelo.php */
namespace Sistema\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class dashboardModelo extends Modelo
{
    public function __construct() {
        parent::__construct();
    }
	
	public function dashboard()
	{
		return true;
	}
}