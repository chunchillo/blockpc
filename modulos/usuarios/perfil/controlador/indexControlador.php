<?php
/* Clase indexControlador.php */
namespace Usuarios\Perfil\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;

final class indexControlador extends Controlador
{
    private $_modelo;
    private $_token;
	private $_plantilla;

    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo('index');
        $this->_token = $this->genToken();
        $this->_plantilla = PLANTILLA_ADMINISTRADOR;
        $this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
        $this->_vista->asignar('fecha', date('Y-m-d'));
    }
	
	public function index()
	{
		try {
			$this->_acl->acceso('general_acces');
			$this->_vista->asignar('titulo', 'Usuario');
			$this->_vista->asignar('token', $this->_token);
			$this->_vista->asignar('icono', '<i class="fa fa-user" aria-hidden="true"></i>');
			$this->_vista->asignar('url_editar', URL_BASE . 'usuarios/perfil/editar');
			$ahora = time() - Sesion::get('inicioSesion');
            $usuario = Sesion::get('usuario');
            if ( file_exists(RUTA_ARCHIVOS_USUARIOS . $usuario['alias'] . DS . $usuario['imagen']) ) {
                $this->_vista->asignar('ruta_imagen', $usuario['ruta_imagen']);
            } else {
                $this->_vista->asignar('ruta_imagen', URL_ARCHIVOS_USUARIOS . DS . 'usuario.png');
            }
            $this->_vista->asignar('subtitulo', "Perfil Usuario {$usuario['alias']}");
            $this->_vista->asignar('idSesion', Sesion::getSessionId());
            $this->_vista->asignar('email', $usuario['email']);
            $this->_vista->asignar('usuario', $usuario['alias']);
            $this->_vista->asignar('apellido', $usuario['apellido']);
            $this->_vista->asignar('telefono', $usuario['telefono']);
            $this->_vista->asignar('cargo', $usuario['cargo']);
            $this->_vista->asignar('nombre', $usuario['nombre']);
			$this->_vista->asignar('tiempo', $this->tiempoDeSesion($ahora));
            $this->_vista->asignar('resumen', nl2br($usuario['resumen']));
			$funcion = $this->cargarLibreria('Funciones');
            $this->_vista->asignar('creado_el', $funcion->fecha($usuario['creado']));
            
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
			$error = $this->cargarHTML('error', ['error' => $e->getMessage()], $this->_plantilla);
            $this->_vista->asignar('error', $error);
		}
		$this->_vista->setCSS(['perfiles']);
		$this->_vista->setJS(['jquery.blockUI', 'correo', 'eliminar']);
		$this->cargarPagina($this->_vista->renderizar('index', 'perfil', $this->_plantilla));
	}
  
    private function tiempoDeSesion($secs)
    {
        $ret = [];
        if ( $secs == 0 ) {
            $ret[] = '0s';
        } else {
            $bit = array(
                'y' => $secs / 31556926 % 12,
                'w' => $secs / 604800 % 52,
                'd' => $secs / 86400 % 7,
                'h' => $secs / 3600 % 24,
                'm' => $secs / 60 % 60,
                's' => $secs % 60
            );
            foreach($bit as $k => $v) {
                if($v > 0)
                    $ret[] = $v . $k;
            }
        }
        return join(' ', $ret) . ' atrás.';
    }
	
	public function truncar()
	{
		try {
			$this->_acl->acceso('general_acces');
			if ( $this->_token != $this->post('token') ) {
				throw new \Exception("Llave de formulario no corresponde!");
			}
			if ( !$this->_modelo->truncar() ) {
				throw new \Exception("No se pudieron vaciar las tablas!");
			}
			$resultado['ok'] = true;
			$resultado['mensaje'] = "Tablas vaciadas correctamente";
		} catch(\Exception $e) {
			$resultado['ok'] = false;
			$resultado['mensaje'] = $e->getMessage();
		}
		header('Content-Type: application/json; charset=utf-8', true);
		echo json_encode($resultado);
		exit;
    }
    
    public function correo()
    {
        try {
            $this->_acl->acceso('general_acces');
            if ( $this->_token != $this->post('token') ) {
                throw new \Exception('Llave no valida!');
            }
            $correo = $this->cargarLibreria('Correo');
            $funciones = $this->cargarLibreria('Funciones');
            $correo->setFrom(CORREO_CONTACTO, NOMBRE_CONTACTO);
            $mensaje = "Mensaje Prueba";
            $to_user = "juan.marchant@gmail.com";
            $to_email = "juan.marchant@gmail.com";
            $asunto = "Mensaje Prueba (No Responder)";
            $correo->addAddress($to_email, $to_user);
            $plantilla = [
                'ruta_img' => $this->_vista->setImagen('controlando.png'),
                'nombre' => NOMBRE_CONTACTO,
                'correo' => CORREO_CONTACTO,
                'asunto' => $asunto,
                'mensaje' => $mensaje,
                'fecha' => $funciones->fecha(date('Y-m-d'))
            ];
            $PlantillaServer = $this->cargarVista('plantilla_envio', $plantilla);
            if ( !PRODUCCION ) {
                echo $PlantillaServer;
                exit;
            } else {
                $correo->content($asunto, $PlantillaServer, $mensaje);
                if ( !$correo->send() ) {
                    Sesion::set("error", "Hubo un inconveniente al enviar el correo. Contacta a un administrador");
                    $this->redireccionar("ordendetrabajo");
                    exit;
				}
			}
            $resultado['ok'] = true;
            $resultado['mensaje'] = "Correo enviado";
        } catch(\Exception $e) {
            $resultado['ok'] = false;
            $resultado['error'] = $e->getMessage();
        }
        header('Content-Type: application/json; charset=utf-8', true);
        echo json_encode($resultado);
        exit;
    }
	
}