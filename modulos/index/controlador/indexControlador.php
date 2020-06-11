<?php
/* Clase indexControlador.php */
namespace Index\Controlador;

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
		$this->_plantilla = PLANTILLA_POR_DEFECTO;
    }
	
	public function index()
	{
		try {
			$this->_vista->asignar('titulo', 'Control Index');
			# Cargar una Libreria
			$funcion = $this->cargarLibreria("Funciones");
			$fecha = $funcion->fecha(date("Y-m-d"));
			$this->_vista->asignar('hoy', $fecha);
			
			$browser = $funcion->get_browser_name();
			$this->_vista->asignar('browser', $browser);
			
		} catch(\Exception $e) {
			$error = $this->cargarHTML('error', ['error' => $e->getMessage()], $this->_plantilla);
            $this->_vista->asignar('error', $error);
		}
		$this->_vista->setCSS(['estado']);
		$this->_vista->setJS(['estado']);
		$this->cargarPagina($this->_vista->renderizar('index', 'inicio', $this->_plantilla));
	}
	
}