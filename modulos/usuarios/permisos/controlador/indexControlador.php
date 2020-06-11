<?php
/* Clase indexControlador.php */
namespace Usuarios\Permisos\Controlador;

use Blockpc\Clases\Controlador as Controlador;
use Blockpc\Clases\Sesion as Sesion;

final class indexControlador extends Controlador
{
    private $_modelo;
    private $_plantilla;

    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo('index');
        $this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
    }

    public function index() {
        try {
            $this->redireccionar('usuarios/permisos/listar');
            $this->_acl->acceso('general_acces');
            $this->_vista->asignar('titulo', "Control de Permisos");
            $this->_vista->asignar('subtitulo', "Control de Permisos de Usuario");
            $this->listarPermisos();
        } catch(\Exception $e) {
            $error = $this->cargarHTML('error', array('error' => $e->getMessage()));
            $this->_vista->asignar('error', $error);
        }
        $this->cargarPagina($this->_vista->renderizar("index", "permisos", "backend"));
    }

    private function listarPermisos() {
        $permisos = $this->_acl->getPermisos();
        $html = "";
        $contador = 0;
        foreach($permisos as $permiso) {
            $permiso['contador'] = ++$contador;
            $permiso['valor'] = ($permiso['valor']) ? "Si" : "No";
            $html .= $this->cargarVista('tabla', $permiso);
        }
        $this->_vista->asignar('tbody', $html);
        $usuario = Sesion::get('usuario');
        $role = $this->_modelo->cargarRole($usuario['role']);
        $this->_vista->asignar('role', $role);
    }
}