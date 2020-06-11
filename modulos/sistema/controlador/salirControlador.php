<?php
/* Clase salirControlador.php */
namespace Sistema\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;

final class salirControlador extends Controlador {
    private $_modelo;
  
    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo('salir');
    }

    public function index()
    {
        try {
            $this->_acl->acceso('general_acces');
            $this->_modelo->salir(Sesion::getUsuario('id'));
            Sesion::destruir();
            header('Location: ' . URL_BASE);
        } catch(\Exception $e) {
            throw new \Exception($e);
        }
    }
  
}