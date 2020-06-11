<?php
/* Clase eliminarControlador.php */
namespace Usuarios\Index\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;

final class eliminarControlador extends Controlador
{
    private $_modelo;
    private $_token;
    private $_plantilla;
    
    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo('eliminar');
        $this->_token = $this->genToken();
        $this->_plantilla = PLANTILLA_ADMINISTRADOR;
        $this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
        $this->_vista->asignar('fecha', date('Y-m-d'));
    }
    
    public function index(int $id = 0) {
        try {
            $this->_acl->acceso('admin_acces');
            if ( !$id ) {
                Sesion::set('error', "El identificador no es valido!");
                $this->redireccionar("usuarios/activos");
            }
            if ( $id == Sesion::getUsuario('id') ) {
                Sesion::set('error', "Tu no te puedes eliminar del sistema! Comunicate con un administrador");
                $this->redireccionar("usuarios/activos");
            }
            $this->_vista->asignar('titulo', 'Eliminar Usuario');
            $this->_vista->asignar('token', $this->_token);
            $this->validar($id);
        } catch(\Exception $e) {
            Sesion::set('error', $e->getMessage());
            $this->redireccionar("usuarios/activos");
        }
        $this->cargarPagina($this->_vista->renderizar("eliminar", "usuarios", $this->_plantilla));
    }
    
    private function validar($id)
    {
        $idUsuario = $id ?: Sesion::getUsuario('id');
        $usuario = $this->_modelo->buscarUsuarioId($idUsuario);
        $this->_vista->asignar('id', $idUsuario);
        $this->_vista->asignar('alias', $usuario['alias']);
        $this->_vista->asignar('cargo', $usuario['cargo']);
        $this->_vista->asignar('nombre', $usuario['nombre']);
        $this->_vista->asignar('apellido', $usuario['apellido']);
        $this->_vista->asignar('rut', $usuario['rut']);
        $this->_vista->asignar('telefono', $usuario['telefono']);
        $this->_vista->asignar('celular', $usuario['celular']);
        $this->_vista->asignar('email', $usuario['email']);
        $this->_vista->asignar('direccion', $usuario['direccion']);
        $this->_vista->asignar('region', $usuario['region']);
        $this->_vista->asignar('provincia', $usuario['provincia']);
        $this->_vista->asignar('comuna', $usuario['comuna']);
        if ($token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING) ) {
            if ( $this->_token != $token) {
                Sesion::set('error', "Llave de formulario no corresponde!");
                $this->redireccionar("usuarios/activos");
            }
            if ( $id != filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING) ) {
                Sesion::set('error', "Identificador de formulario no corresponde!");
                $this->redireccionar("usuarios/activos");
            }
            $codigo = $this->generarCodigo();
			$fecha = date_create('U');
            $deleted_at = date_format($fecha, 'Y-m-d H:i:s');
            $user_id = Sesion::getUsuario('id');
            if ( !$this->_modelo->eliminarUsuario($id, $codigo, $deleted_at, $user_id) ) {
                Sesion::set('error', "El usuario no se pudo eliminar!");
                $this->redireccionar("usuarios/activos");
            }
			rmdir(RUTA_ARCHIVOS_USUARIOS . $usuario['usuario']);
            Sesion::set('mensaje', "El usuario se elimino correctamente");
            $this->redireccionar("usuarios/activos");
        }
    }


}