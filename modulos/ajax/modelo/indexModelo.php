<?php
/* Clase indexModelo.php */
namespace Ajax\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class indexModelo extends Modelo
{
    public function __construct() {
        parent::__construct();
    }

    public function index()
    {
        return true;
    }
}