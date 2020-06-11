<?php
/* Clase eliminarControlador.php */
namespace Usuarios\Permisos\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;
use Blockpc\Librerias\Gump;

final class eliminarControlador extends Controlador
{
    private $_modelo;
    private $_token;
    private $_plantilla;

    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo("eliminar");
        $this->_token = $this->genToken();
        $this->_plantilla = PLANTILLA_ADMINISTRADOR;
        $this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
		$this->_vista->asignar('fecha', date('Y-m-d'));
    }
  
    public function index(int $id = 0) {
        try {
            $this->_acl->acceso('admin_acces');
            $this->_vista->asignar('titulo', "Eliminar Permiso");
            $this->_vista->asignar('token', $this->_token);

            if ( !$id ) {
                Sesion::set("error", "Se esperaba un Identificador de Permiso!");
                $this->redireccionar("usuarios/permisos/listar");
            }
            $this->validar($id);
            if (Sesion::get("mensaje")) {
                $mensaje = $this->cargarHTML('mensaje', array('mensaje' => Sesion::get("mensaje")), $this->_plantilla);
                $this->_vista->asignar('mensaje', $mensaje);
                Sesion::destruir('mensaje');
            }
            if (Sesion::get("error_form")) {
                $error = Sesion::get("error_form");
                Sesion::destruir('error_form');
                throw new \Exception($error);
            }
        } catch(\Exception $e) {
            $error = $this->cargarHTML('error', array('error' => $e->getMessage()));
            $this->_vista->asignar('error', $error);
        }
        $this->cargarPagina($this->_vista->renderizar("index", "usuarios", $this->_plantilla));
    }
  
    private function validar(int $id)
    {
        $permiso = $this->_modelo->cargarPermiso($id);
        if ( !$permiso ) {
            Sesion::set("error", "No existe el Permiso buscado!");
            $this->redireccionar("usuarios/permisos/listar");
        }
        $this->_vista->asignar('check', $permiso['editable'] ? 'checked' : '');
        $this->_vista->asignar('disabled', $permiso['editable'] ? '' : 'disabled');
        $this->_vista->asignar('id', $permiso['id']);
        $this->_vista->asignar('permiso', $permiso['permiso']);
        $this->_vista->asignar('llave', $permiso['llave']);
        $this->_vista->asignar('descripcion', $permiso['descripcion']);
        $this->_vista->asignar('btndisabled', '');
        if ( $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING) ) {
            if ( $token !== $this->_token ) {
                Sesion::set("error", "Llave del formulario no corresponde");
                $this->redireccionar("usuarios/permisos/listar");
            }
            if ( $id != $this->post('id') ) {
                Sesion::set("error", "Identificador de Permiso incorrecto!");
                $this->redireccionar("usuarios/permisos/listar");
            }
            if ( Sesion::getUsuario('role') != 1 && !$permiso['editable'] ) {
                Sesion::set("error", "El Permiso <b>{$permiso['permiso']}</b> no se puede eliminar!");
                $this->redireccionar("usuarios/permisos/listar");
            }
            if ( !$this->_modelo->eliminar($id) ) {
                throw new \Exception("El permiso no se pudo eliminar!");
            }
            Sesion::set("mensaje", "El permiso <b>{$permiso['permiso']}</b> fue eliminado correctamente.<br>La llave <b>{$permiso['llave']}</b> ha quedado deshabilitada y pueden surgir errores en las funcionalidades que la implementen.<br>Avisa a tu desarrollador de este cambio.");
            $this->redireccionar("usuarios/permisos/listar");
        }
    }
}