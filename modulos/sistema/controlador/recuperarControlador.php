<?php
/* Clase recuperarControlador.php */
namespace Sistema\Controlador;

use Blockpc\Clases\Controlador;

final class recuperarControlador extends Controlador {
    private $_modelo;
    private $_token;

    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo('recuperar');
        $this->_token = $this->genToken();
        $this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
    }
  
    public function index() {
        try {
            //$this->redireccionar('sistema/login');
            $this->_vista->asignar('titulo', 'Recuperar Clave');
            $this->_vista->asignar('token', $this->_token);
            $this->recuperarClave();
        } catch(\Exception $e) {
            $error = $this->cargarVista('error', array('error' => $e->getMessage()));
            $this->_vista->asignar('error', $error);
        }
        $this->_vista->setCSS(['recuperar']);
        $this->cargarPagina($this->_vista->renderizar("index", "login"));
    }
  
    private function recuperarClave()
	{
        $this->_vista->asignar('txtCorreo', '');
        $this->_vista->asignar('txtusuario', '');
        if ( filter_input(INPUT_POST, 'token') == $this->_token ) {
            $this->_vista->asignar('post', print_r($_POST, true));
            if ( !$correo = filter_input(INPUT_POST, 'txtCorreo', FILTER_VALIDATE_EMAIL) ) {
                throw new \Exception("Se esperaba una dirección de correo valida!");
            }
            if ( !$alias = filter_input(INPUT_POST, 'txtUsuario', FILTER_SANITIZE_STRING) ) {
                throw new \Exception("Se esperaba una dirección de correo valida!");
            }
            if ( !$usuario = $this->_modelo->recuperarUsuario($alias, $correo) ) {
                throw new \Exception("El correo o el nombre no existen en la base de datos!");
            }
            if ( !$usuario['activado'] ) {
                throw new \Exception("Esta cuenta no esta activada!<br>Comunicate con un administrador!");
            }
            if ( $this->enviarCorreo($usuario) ) {
                $nuevaClave = password_hash($usuario['codigo'], PASSWORD_BCRYPT, array("cost" => 10));
                $this->_modelo->actualizarDatosUsuario($usuario['id'], $nuevaClave);
            }
            $mensaje = $this->cargarVista('mensaje', array('mensaje' => "Te enviamos un correo<br>Revisa tu bandeja de entrada"));
            $this->_vista->asignar('mensaje', $mensaje);
        }
    }
  
    private function enviarCorreo($datosUsuario)
	{
        $funciones = $this->cargarLibreria('Funciones');
        $url = URL_BASE . "usuarios/activar/{$datosUsuario['id']}/{$datosUsuario['codigo']}";
        $fecha = $funciones->fecha();
        $mensaje = "<p>Saludos <b>{$datosUsuario['usuario']}</b></p>";
        $mensaje .= "Enviaste una solicitud para recuperar tu clave en <b>www.blockpc.cl</b>, con fecha de {$fecha}<br>";
        $mensaje .= "Tu clave nueva es:<br><small>{$datosUsuario['codigo']}</small><br>";
        $mensaje .= "Necesitas activar la cuenta para poder ingresar al sistema y modificar tus datos.<br>";
        $mensaje .= "URL para activación: {$url}<br><br><em>Recuerda que puedes cambiar la clave desde tu perfil de usuario.</em>";
        $to_user = $datosUsuario['nombre'];
        $to_email = $datosUsuario['email'];
        $from_user = "Soporte Blockpc";
        $from_email = "soporte@blockpc.cl";
        $titulo = "Recuperar Clave (No Responder)";
        if ( !$funciones->mailTo($to_user, $to_email, $from_user, $from_email, $titulo, $mensaje) ) {
            throw new \Exception("No se pudo enviar el correo al nuevo usuario!");
        }
        return true;
    }
  
}