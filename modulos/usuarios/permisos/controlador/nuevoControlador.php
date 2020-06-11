<?php
/* Clase nuevoControlador.php */
namespace Usuarios\Permisos\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;
use Blockpc\Librerias\Gump;

final class nuevoControlador extends Controlador
{
    private $_modelo;
    private $_token;
    private $_plantilla;

    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo("nuevo");
		$this->_plantilla = "backend";
        $this->_token = $this->genToken();
        $this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
        $this->_vista->asignar('fecha', date('Y-m-d'));
    }
  
    public function index() {
        try {
            $this->_acl->acceso('admin_acces');
            $this->_vista->asignar('titulo', "Nuevo Permiso");
            $this->_vista->asignar('token', $this->_token);

            $this->validar();

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
  
    private function validar() {
        $this->_vista->asignar('input_checked', $this->post('editable') ? 'checked' : '');
        $this->_vista->asignar('permiso', $this->post('permiso', ''));
        $this->_vista->asignar('llave', $this->post('llave', ''));
        $this->_vista->asignar('descripcion', nl2br($this->post('descripcion')) ?? '');
        if ( $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING) ) {
            if ( $token !== $this->_token ) {
                Sesion::set("error", "Llave del formulario no corresponde");
                $this->redireccionar("usuarios/permisos/listar");
            }
            $gump = new Gump('es');
            // set validation rules
            $gump->validation_rules([
                'permiso' => 'required|alpha_space|max_len,64|min_len,3',
                'llave' => 'required|alpha|max_len,32|min_len,3',
                'descripcion' => 'alpha_numeric_space|max_len,256|min_len,3'
            ]);
            // set filter rules
            $gump->filter_rules([
                'permiso' => 'trim|sanitize_string',
                'llave' => 'trim|sanitize_string',
                'descripcion' => 'trim|sanitize_string',
            ]);
            $valid_data = $gump->run($_POST);
            if ($gump->errors()) {
                $error = implode("<br>", $gump->get_readable_errors());
                throw new \Exception($error);
            } else {
                $permiso = $this->post('permiso');
                $llave = $this->post('llave');
                $editable = $this->post('editable') ? 1 : 0;
                if ( $this->_modelo->checkPermiso($permiso) ) {
                    throw new \Exception("El permiso <b>{$permiso}</b> ya existe!");
                }
                if ( $this->_modelo->checkLlave($llave) ) {
                    throw new \Exception("La llave <b>{$llave}</b> ya existe!");
                }
                $nuevo = [
                    'permiso' => $valid_data['permiso'],
                    'llave' => "{$valid_data['llave']}_acces",
                    'descripcion' => $valid_data['descripcion'] ?: 'Sin Descripción',
                    'editable' => $editable
                ];
                if ( !$this->_modelo->nuevo($nuevo) ) {
                    throw new \Exception("El nuevo permiso <b>{$permiso}</b> no se pudo agregar!");
                }
                Sesion::set('mensaje', "Se ha creado un permiso. <b>{$permiso}</b>");
                $this->redireccionar('usuarios/permisos/listar');
            }
        }
    }
}