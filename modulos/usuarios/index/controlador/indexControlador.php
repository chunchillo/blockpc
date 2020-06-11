<?php
/* Clase indexControlador.php */
namespace Usuarios\Index\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;

final class indexControlador extends Controlador
{
	private $_modelo;
    private $_token;
    private $_plantilla;
    private $_pagina;
	
	public function __construct() {
		$this->construir();
		$this->_modelo = $this->cargarModelo('index');
        $this->_token = $this->genToken();
        $this->_plantilla = ( Sesion::get('autorizado') ) ? PLANTILLA_ADMINISTRADOR : PLANTILLA_POR_DEFECTO;
        $this->_pagina = ( Sesion::get('autorizado') ) ? 'sistema' : 'local';
        $this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
        $this->_vista->asignar('fecha', date('Y-m-d'));
	}

	public function index() {
		try {
			$this->_vista->asignar('titulo', 'Usuarios');
            $this->_vista->asignar('icono', '<i class="fa fa-users" aria-hidden="true"></i>');
			$usuarios = $this->_modelo->cargarUsuarios(Sesion::getUsuario('role') ?: 2);
			echo "<pre>"; print_r($usuarios); echo "</pre>"; exit;
		} catch(\Exception $e) {
			$error = $this->cargarVista('error', array('error' => $e->getMessage()));
			$this->_vista->asignar('error', $error);
		}
		$this->cargarPagina($this->_vista->renderizar($this->_pagina, 'usuarios', $this->_plantilla));
	}
  
}