<?php
/* Clase errorControlador.php */
namespace Index\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;

final class errorControlador extends Controlador
{
    private $_plantilla;

    public function __construct() {
        $this->construir();
		$this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
        $this->_vista->asignar('fecha', date('Y-m-d'));
        $this->_plantilla = ( Sesion::get('autorizado') ) ? PLANTILLA_ADMINISTRADOR : PLANTILLA_POR_DEFECTO;
    }

    public function index() {
        try {
			$this->_vista->asignar('titulo', 'Error');
			$codigo = Sesion::get('codigo') ?: 1000;
			Sesion::destruir('codigo');
			$error = Sesion::get('error') ?: "Código de error no encontrado. Consulte con el administrador.";
			Sesion::destruir('error');
			$this->_vista->asignar('titulo_error', "Error {$codigo}");
			$this->_vista->asignar('mensaje_error', $error);
        } catch(\Throwable $e) {
            $this->_vista->asignar('error', $e->getMessage());
        }
		$this->_vista->setCSS(['error']);
		$this->_vista->setJS(['error']);
        $this->cargarPagina($this->_vista->renderizar('error', '', $this->_plantilla));
    }
  
}