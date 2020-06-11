<?php
/* Clase chileModelo.php */
namespace Ajax\Modelo;

use Blockpc\Clases\Modelo as Modelo;

final class chileModelo extends Modelo
{
    public function __construct() {
        parent::__construct();
    }

    public function chile()
    {
        return true;
    }

    public function regiones()
    {
        return $this->_db->query("SELECT * FROM region;")->fetchAll(\PDO::FETCH_OBJ);
    }

    public function provincias(int $region = 0)
    {
        return $this->_db->query("SELECT * FROM provincia WHERE region = {$region};")->fetchAll(\PDO::FETCH_OBJ);
    }

    public function comunas(int $provincia = 0)
    {
        return $this->_db->query("SELECT * FROM comuna WHERE provincia = {$provincia};")->fetchAll(\PDO::FETCH_OBJ);
    }
}