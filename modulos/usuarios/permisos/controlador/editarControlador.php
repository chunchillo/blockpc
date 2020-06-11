<?php
/* Clase editarControlador.php */
namespace Usuarios\Permisos\Controlador;

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
            $this->_vista->asignar('titulo', "Editar Permiso");
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
                $this->redireccionar("usuarios/roles/listar");
            }
            $gump = new Gump('es');
            // set validation rules
            $gump->validation_rules([
                'permiso' => 'required|alpha_space|max_len,64|min_len,3',
                'llave' => 'required|alpha|max_len,32|min_len,3',
                'descripcion' => 'alpha_numeric_space|max_len,256|min_len,3',
                'id' => "required|min_numeric,{$id}|max_numeric,{$id}",
            ]);
            // set filter rules
            $gump->filter_rules([
                'permiso' => 'trim|sanitize_string',
                'llave' => 'trim|sanitize_string',
                'descripcion' => 'trim|sanitize_string',
                'id' => 'trim|sanitize_numbers'
            ]);
            $valid_data = $gump->run($_POST);
            if ($gump->errors()) {
                $error = implode("<br>", $gump->get_readable_errors());
                throw new \Exception($error);
            } else {
                $permiso = $this->post('permiso');
                $llave = $this->post('llave');
                $editable = $this->post('editable') ? 1 : 0;
                if ( $this->_modelo->checkPermiso($permiso, $id) ) {
                    throw new \Exception("El permiso <b>{$permiso}</b> ya existe!");
                }
                if ( $this->_modelo->checkLlave($llave, $id) ) {
                    throw new \Exception("La llave <b>{$llave}</b> ya existe!");
                }
                $editar = [
                    'permiso' => $valid_data['permiso'],
                    'llave' => "{$valid_data['llave']}_acces",
                    'descripcion' => $valid_data['descripcion'] ?: 'Sin Descripción',
                    'editable' => $editable
                ];
                if ( !$this->_modelo->editar($editar, $id) ) {
                    throw new \Exception("El permiso <b>{$permiso}</b> no se pudo editar!");
                }
                Sesion::set('mensaje', "Se ha editado un permiso. <b>{$permiso}</b>");
                $this->redireccionar('usuarios/permisos/listar');
            }
        }
    }
}