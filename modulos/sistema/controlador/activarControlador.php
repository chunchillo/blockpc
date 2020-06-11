<?php
/* Clase activarControlador.php */
namespace Sistema\Controlador;

use Blockpc\Clases\Controlador;

final class activarControlador extends Controlador {
	private $_modelo;

	public function __construct() {
	$this->construir();
	$this->_modelo = $this->cargarModelo('activar');
	$this->_vista->asignar('error', '');
	$this->_vista->asignar('mensaje', '');
	}
  
	public function index() {
		try {
			$this->_vista->asignar('titulo', 'Activación de Cuenta');
			$this->_vista->asignar('icono', '<i class="fa fa-users" aria-hidden="true"></i>');
			$this->_vista->asignar('url_login', URL_BASE . 'sistema/login');
			if ( func_num_args() != 2 ) {
				throw new \Exception("Numero de parámetros invalido");
			}
			list($id, $codigo) = func_get_args();
			if ( !$id = filter_var($id, FILTER_VALIDATE_INT) ) {
				throw new \Exception("Se esperaba un entero valido");
			}
			if ( !$codigo = filter_var($codigo, FILTER_SANITIZE_STRING) ) {
				throw new \Exception("Se esperaba un código valido");
			}
			if ( !$this->_modelo->validarCodigo($codigo) ) {
				throw new \Exception("El código de activación es erróneo!");
			}
			$activado = $this->_modelo->validarUsuario($id, $codigo);
			if ( $activado ) {
				throw new \Exception("El usuario ya esta activado");
			}
			$nuevoCodigo = $this->generarCodigo();
			$this->_modelo->activarUsuario($id, $nuevoCodigo);
			$mensaje = $this->cargarHTML('mensaje', ['mensaje' => 'Tu cuenta a sido activada<br>Ya puedes ingresar al sistema con la clave enviada a tu correo.<br>Gracias.']);
			$this->_vista->asignar('mensaje', $mensaje);
			$this->_vista->asignar('estado_cuenta', 'Cuenta Activada!!');
			$this->_vista->asignar('clase', 'btn btn-xl btn-primary');
		} catch(\Exception $e) {
			$this->_vista->asignar('estado_cuenta', 'Error de Activación');
			$this->_vista->asignar('clase', 'btn btn-xl btn-primary disabled');
			$error = $this->cargarHTML('error', array('error' => $e->getMessage()));
			$this->_vista->asignar('error', $error);
		}
		$this->_vista->setCSS(['activar']);
		$this->cargarPagina($this->_vista->renderizar("index", "index"));
	}
  
}