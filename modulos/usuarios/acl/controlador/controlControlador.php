<?php
/* Clase controlControlador.php */
namespace Usuarios\Acl\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;

final class controlControlador extends Controlador
{
    private $_modelo;
    private $_token;
    private $_plantilla;

    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo('control');
        $this->_plantilla = PLANTILLA_ADMINISTRADOR;
        $this->_token = $this->genToken();
        $this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
        $this->_vista->asignar('tbody', '<tr><td class="text-center" colspan="5">Debes seleccionar un <b>ROL</b></td></tr>');
        $this->_vista->asignar('fecha', date('Y-m-d'));
    }
  
    public function index() {
        try {
            $this->_acl->acceso('admin_acces');
            $this->_vista->asignar('titulo', "Control ACL");
            $this->_vista->asignar('token', $this->_token);
            if ( filter_input(INPUT_POST, 'token') == $this->_token ) {
                $this->validar();
            }
            $this->cargarRoles();
        } catch(\Exception $e) {
            $error = $this->cargarHTML('error', array('error' => $e->getMessage()), $this->_plantilla);
            $this->_vista->asignar('error', $error);
        }
        $this->_vista->setJS(['control']);
        $this->cargarPagina($this->_vista->renderizar("index", "usuarios", $this->_plantilla));
    }
  
    private function validar() {
        $contador = 0;
        $idRole = filter_input(INPUT_POST, 'role', FILTER_VALIDATE_INT);
        $role = $this->_modelo->buscarRoleID($idRole);
        unset($_POST['token'], $_POST['role']);
        foreach ($_POST as $llave => $valor) {
            $idPermiso = $this->_modelo->buscarPermisoID($llave);
            if ( $valor === 'x') {
                $eliminar = ['idRole' => $idRole, 'idPermiso' => $idPermiso];
                if ( $this->_modelo->eliminarPermisoRole($eliminar) ) $contador++;
            } else {
                $editar = ['idRole' => $idRole, 'idPermiso' => $idPermiso, 'valor' => $valor];
                if ( $this->_modelo->editarPermisoRole($editar) ) $contador++;
            }
        }
        if ( $contador == $this->_modelo->contarPermisos() ) {
            $mensaje = $this->cargarHTML('mensaje', ['mensaje' => "Se ha modificado el Role <b>{$role}</b>"]);
            $this->_vista->asignar('mensaje', $mensaje);
        }
    }
  
    private function cargarRoles() {
        $roles = $this->_modelo->cargarRoles(Sesion::getUsuario('role'));
        $html = "";
        foreach($roles as $role) {
            $html .= $this->cargarVista('roles', $role);
        }
        $this->_vista->asignar('roles', $html);
    }

}