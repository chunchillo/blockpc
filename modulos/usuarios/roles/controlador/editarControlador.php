<?php
/* Clase editarControlador.php */
namespace Usuarios\Roles\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;
use Blockpc\Librerias\Gump;

final class editarControlador extends Controlador
{
    private $_modelo;
    private $_token;
    private $_plantilla;

    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo("editar");
        $this->_token = $this->genToken();
        $this->_plantilla = PLANTILLA_ADMINISTRADOR;
        $this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
        $this->_vista->asignar('fecha', date('Y-m-d'));
    }
  
    public function index(int $id = 0) {
        try {
            $this->_acl->acceso('admin_acces');
            $this->_vista->asignar('titulo', "Editar Rol");
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
            $error = $this->cargarHTML('error', array('error' => $e->getMessage()), $this->_plantilla);
            $this->_vista->asignar('error', $error);
        }
        $this->cargarPagina($this->_vista->renderizar("index", "usuarios", $this->_plantilla));
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
            $gump = new Gump('es');
            // set validation rules
            $gump->validation_rules([
                'role' => 'required|alpha_space|max_len,64|min_len,4',
                'descripcion' => 'alpha_numeric_space|max_len,256|min_len,4',
                'id' => "required|min_numeric,{$id}|max_numeric,{$id}",
            ]);
            // set filter rules
            $gump->filter_rules([
                'role' => 'trim|sanitize_string',
                'descripcion' => 'trim|sanitize_string',
                'id' => 'trim|sanitize_numbers'
            ]);
            $valid_data = $gump->run($_POST);
            if ($gump->errors()) {
                $error = implode("<br>", $gump->get_readable_errors());
                throw new \Exception($error);
            } else {
                $role = $this->post('role');
                $editable = $this->post('editable') ? 1 : 0;
                if ( $this->_modelo->checkRol($role, $id) ) {
                    throw new \Exception("El Rol <b>{$role}</b> ya existe!");
                }
                $nuevo = [
                    'role' => $valid_data['role'],
                    'descripcion' => $valid_data['descripcion'] ?: 'Sin Descripción',
                    'editable' => $editable
                ];
                if ( !$this->_modelo->editar($nuevo, $id) ) {
                    throw new \Exception("El Rol <b>{$role}</b> no se pudo editar!");
                }
                Sesion::set('mensaje', "Se ha editado un Rol. <b>{$role}</b>");
                $this->redireccionar('usuarios/roles/listar');
            }
        }
    }
}