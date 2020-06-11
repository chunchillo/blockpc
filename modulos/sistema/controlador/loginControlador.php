<?php
/* Clase loginControlador.php */
namespace Sistema\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;

final class loginControlador extends Controlador
{
    private $_modelo;
    private $_token;
    private $_plantilla;

    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo('login');
        $this->_token = $this->genToken();
        $this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
        $this->_vista->asignar('txtNombre', '');
        $this->_vista->asignar('txtClave', '');
        $this->_vista->asignar('check', '');
        $this->_plantilla = PLANTILLA_POR_DEFECTO;
    }
  
    public function index()
    {
        try {
            if ( Sesion::get('autorizado') ) { /* Validar Usuario ya logeado */
                $this->redireccionar('sistema/dashboard');
            }
            $this->_vista->asignar('titulo', "Login de usuarios");
            $this->_vista->asignar('token', $this->_token);
            $this->validarFormularioLogin();
            
            if (Sesion::get('mensaje')) {
                $mensaje = $this->cargarHTML('mensaje', array('mensaje' => Sesion::get('mensaje')), $this->_plantilla);
                $this->_vista->asignar('mensaje', $mensaje);
                Sesion::destruir('mensaje');
            }
            if (Sesion::get('error')) {
                $error = Sesion::get('error');
                Sesion::destruir('error');
                throw new \Exception($error);
            }
        } catch(\Exception $e) {
            $error = $this->cargarHTML('error', array('error' => $e->getMessage()));
            $this->_vista->asignar('error', $error);
        }
        $this->_vista->setCSS(['login']);
        $this->cargarPagina($this->_vista->renderizar('index', 'login', $this->_plantilla));
    }
  
    private function validarFormularioLogin()
    {
        if ( $this->_token === filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING) ) {
			$recordarme = false;
            $nombre = filter_input(INPUT_POST, 'txtNombre', FILTER_SANITIZE_STRING);
            $clave = filter_input(INPUT_POST, 'txtClave', FILTER_SANITIZE_STRING);
            if ( !$nombre || !$clave ) {
                throw new \Exception("Usuario / Clave no son correctos");
            }
            if ( !$usuario = $this->_modelo->validarLogin($nombre) ) {
                throw new \Exception("Usuario / Clave no son validos");
            }
            if ( !$usuario['activado'] ) {
                throw new \Exception("Este usuario no esta activado");
            }
            if ( !password_verify($clave, $usuario['clave']) ) {
                throw new \Exception("Usuario / Clave no coincide");
            }
            if ( filter_input(INPUT_POST, 'check', FILTER_SANITIZE_STRING) ) {
                $recordarme = true;
            }
			$funcion = $this->cargarLibreria('Funciones');
			$ip = $funcion->get_client_ip();
            $usuario['cargo'] = ucfirst(mb_strtolower($this->_modelo->obtenerCargo($usuario['role'])));
            $this->_modelo->enLinea($usuario['id']);
			$rutaImagenUsuario = RUTA_ARCHIVOS_USUARIOS . $usuario['alias'] . DS . $usuario['imagen'];
			$rutaImagenCargo = RUTA_ARCHIVOS_USUARIOS . $usuario['cargo'] . ".png";
            if ( file_exists($rutaImagenUsuario) ) {
                $usuario['ruta_imagen'] = URL_ARCHIVOS_USUARIOS . $usuario['alias'] . "/" . $usuario['imagen'];
            } else if ( file_exists($rutaImagenCargo) )  {
                $usuario['ruta_imagen'] = URL_ARCHIVOS_USUARIOS . $usuario['cargo'] . ".png";
            } else {
                $usuario['ruta_imagen'] = URL_ARCHIVOS_USUARIOS . "Usuario.png";
                //$usuario['ruta_imagen'] = URL_ARCHIVOS_IMAGENES . "blockpc.png";
            }
            Sesion::iniciarSesion($usuario, $recordarme, $ip); /* Iniciamos la sesión */
            $this->redireccionar('sistema/dashboard');
        }
    }
}