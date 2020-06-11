<?php
/* Clase dashboardControlador.php */
namespace Sistema\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;

final class dashboardControlador extends Controlador
{
    private $_modelo;
    private $_token;
    private $_plantilla;
  
    public function __construct() {
        $this->construir();
        $this->_plantilla = PLANTILLA_ADMINISTRADOR;
        $this->_modelo = $this->cargarModelo('dashboard');
        $this->_token = $this->genToken();
        $this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
        $this->_vista->asignar('fecha', date('Y-m-d'));
    }
    
    public function index()
    {
        try {
			$this->_acl->acceso('general_acces');
			$nombre = Sesion::getUsuario('nombre');
			$this->_vista->asignar('titulo', "Usuario {$nombre}");
            $this->_vista->asignar('token', $this->_token);
            
			$classLoader = require_once( RUTA_VENDOR . 'composer' . DS . 'autoload_classmap.php');
			$clases = [];
			foreach ( $classLoader as $className => $path) {
				if ( strpos($className, 'Controlador') && $className != 'Blockpc\Clases\Controlador') {
					$nombre = str_ireplace(["\\Controlador", "Controlador"], "", $className);
					$spaces = explode("\\", $nombre);
					if ( count($spaces) == 3 ) {
						list($grupo, $modulo, $controlador) = $spaces;
						$clases[$grupo][$modulo][] = $controlador;
					} else {
						list($modulo, $controlador) = $spaces;
						$clases[$modulo][] = $controlador;
					}
				}
			}
			$this->_vista->asignar('clases', print_r($clases, true));
			$directorio = $this->crearDirectorio($clases);
			$this->_vista->asignar('directorio', $directorio);
            if (Sesion::get("mensaje")) {
                $msj = Sesion::get("mensaje");
                $mensaje = $this->cargarHTML('mensaje', array('mensaje' => $msj), $this->_plantilla);
                $this->_vista->asignar('mensaje', $mensaje);
                Sesion::destruir('mensaje');
            }
            if (Sesion::get("error")) {
                $error = Sesion::get("error");
                Sesion::destruir('error');
                throw new \Exception($error);
            }
        } catch(\Exception $e) {
            $error = $this->cargarHTML('error', array('error' => $e->getMessage()), $this->_plantilla);
            $this->_vista->asignar('error', $error);
        }
		$this->_vista->setCSS(['directorio']);
		$this->_vista->setJS(['directorio']);
        $this->cargarPagina($this->_vista->renderizar('index', 'dashboard', $this->_plantilla));
    }
	
	private function crearDirectorio(array $clases, $directorio = "")
	{
		foreach($clases as $grupo => $controlador) {
			if ( is_string($grupo) ) {
				$directorio .= "<li><span class='caret'>{$grupo}</span>";
				if ( is_array($controlador) ) {
					$directorio .= "<ul class='nested'>";
					$directorio .= $this->crearDirectorio($controlador);
					$directorio .= "</ul>";
				}
				$directorio .= "</li>";
			} else {
				$directorio .= "<li>{$controlador}</li>";
			}
		}
		return $directorio;
	}
    
}