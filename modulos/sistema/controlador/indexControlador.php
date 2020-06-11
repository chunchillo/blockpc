<?php
/* Clase indexControlador.php */
namespace Sistema\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;

final class indexControlador extends Controlador
{
    private $_modelo;
    private $_token;
	private $_plantilla;

    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo('index');
        $this->_token = $this->genToken();
		$this->_vista->asignar('error', '');
		$this->_vista->asignar('mensaje', '');
		$this->_vista->asignar('fecha', date("Y-m-d"));
		$this->_plantilla = ( Sesion::get('autorizado') ) ? PLANTILLA_ADMINISTRADOR : PLANTILLA_POR_DEFECTO;
    }
	
	public function index()
	{
		try {
			$this->redireccionar('sistema/login');
		} catch(\Exception $e) {
			$error = $this->cargarHTML('error', ['error' => $e->getMessage()], $this->_plantilla);
            $this->_vista->asignar('error', $error);
		}
		$this->cargarPagina($this->_vista->renderizar('index', 'home', $this->_plantilla));
	}
	
}