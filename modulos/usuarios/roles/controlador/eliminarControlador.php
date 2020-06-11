<?php
/* Clase eliminarControlador.php */
namespace Usuarios\Roles\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;

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
            $this->_vista->asignar('titulo', "Eliminar Rol");
            $this->_vista->asignar('token', $this->_token);
            if ( !$id ) {
                Sesion::set("error", "Se esperaba un Identificador de Permiso!");
                $this->redireccionar("usuarios/roles/listar");
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
        $this->cargarPagina($this->_vista->renderizar("index", "usuarios", "backend"));
    }
  
    private function validar(int $id)
    {
        $role = $this->_modelo->role($id);
        if ( !$role ) {
            Sesion::set("error", "No existe el Rol buscado!");
            $this->redireccionar("usuarios/roles/listar");
        }
        $this->_vista->asignar('check', $role['editable'] ? 'checked' : '');
        $this->_vista->asignar('disabled', $this->_acl->accesoVista('sudo_access') ? '' : 'disabled');
        $this->_vista->asignar('id', $role['id']);
        $this->_vista->asignar('role', $role['role']);
        $this->_vista->asignar('descripcion', $role['descripcion']);
        $this->_vista->asignar('btndisabled', '');
        if ( $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING) ) {
            if ( $token !== $this->_token ) {
                Sesion::set("error", "Llave del formulario no corresponde");
                $this->redireccionar("usuarios/roles/listar");
            }
            if ( $id != $this->post('id') ) {
                Sesion::set("error", "Identificador de Rol incorrecto!");
                $this->redireccionar("usuarios/roles/listar");
            }
            if ( Sesion::getUsuario('role') != 1 && !$role['editable'] ) {
                Sesion::set("error", "El Rol <b>{$role['role']}</b> no se puede eliminar!");
                $this->redireccionar("usuarios/roles/listar");
            }
            if ( !$this->_modelo->eliminar($id) ) {
                throw new \Exception("El Rol no se pudo eliminar!");
            }
            Sesion::set("mensaje", "El Rol <b>{$role['role']}</b> fue eliminado correctamente.<br>Avisa a tu desarrollador de este cambio.");
            $this->redireccionar("usuarios/roles/listar");
        }
    }
}