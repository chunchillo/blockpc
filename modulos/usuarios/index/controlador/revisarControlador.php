<?php
/* Clase revisarControlador.php */
namespace Usuarios\Index\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;

final class revisarControlador extends Controlador
{
	private $_modelo;
	private $_token;
	private $_plantilla;
	private $_mensaje;
	private $_administrador;
  
	public function __construct() {
		$this->construir();
		$this->_modelo = $this->cargarModelo('revisar');
		$this->_token = $this->genToken();
		$this->_vista->asignar('error', '');
		$this->_vista->asignar('mensaje', '');
		$this->_vista->asignar('formulario', '');
		$this->_vista->asignar('fecha', date('Y-m-d'));
		$this->_plantilla = PLANTILLA_ADMINISTRADOR;
		$this->_administrador = Sesion::get('usuario');
	}
  
	public function index($id = 0) {
		try {
			$this->_acl->acceso('admin_acces');
			$this->_vista->asignar('titulo', 'Usuarios - Revisar');
			$this->_vista->asignar('token', $this->_token);

			if ( !$id = filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) ) {
				throw new \Exception("Identificador no valido!");
			}

			$this->cargar($id);

			if (Sesion::get("mensaje")) {
                $mensaje = $this->cargarHTML('mensaje', array('mensaje' => Sesion::get("mensaje")), $this->_plantilla);
                $this->_vista->asignar('mensaje', $mensaje);
                Sesion::destruir('mensaje');
            }
            if (Sesion::get("error")) {
                $error = implode("<br>", Sesion::get("error"));
                Sesion::destruir('error');
                throw new \Exception($error);
            }
		} catch(\Exception $e) {
			$error = $this->cargarHTML('error', array('error' => $e->getMessage()), $this->_plantilla);
			$this->_vista->asignar('error', $error);
		}
		$this->_vista->setURL([
			$this->cargarUrl("assets/blockui/jquery.blockUI.js")
		], 'js');
		$this->_vista->setJS(['revisar']);
		$this->_vista->setCSS(['revisar']);
		$this->cargarPagina($this->_vista->renderizar("revisar", "usuarios", $this->_plantilla));
	}
  
	private function cargar(int $id)
	{
        $usuario = $this->_modelo->buscarUsuario($id);
        $alias = $usuario['alias'];
		$rol = $this->_modelo->obtenerCargo($usuario['role']);
		$this->_vista->asignar('id', $id);
		if ($this->_administrador['id'] == $id) {
			$perfil = URL_BASE . 'usuarios/perfil';
			Sesion::set('error', "No puedes acceder a <b>revisar</b> tu usuario. Para cambiar tus datos debes de acceder desde tu <a href='{$perfil}'>perfil de usuario</a>.");
			$this->redireccionar("usuarios/activos");
		}
		if ($this->_administrador['role'] > $usuario['role']) {
			Sesion::set('error', "No puedes acceder a un usuario <b>{$rol}</b>, tiene un mayor <b>ROL</b> que tu (<b>{$this->_modelo->obtenerCargo($this->_administrador['role'])}</b>).");
			$this->redireccionar("usuarios/activos");
		}
		if ( $usuario['activado'] ) {
			$alert_tipo = "info";
			$alert_mensaje = "El usuario <b>{$alias}</b> esta activado";
			if ( $usuario['role'] == 5 ) {
				$alert_mensaje .= "<br>El cargo <b>{$rol}</b> sera anulado si cambia su ROL actual";
			}
		} else {
			if ( $usuario['deleted_at'] ) {
				$alert_tipo = "danger";
				$alert_mensaje = "El usuario <b>{$alias}</b> esta eliminado";
			} else {
				$alert_tipo = "warning";
				$alert_mensaje = "El usuario <b>{$alias}</b> no esta activado";
			}
		}
		if ($this->_administrador['role'] > $usuario['role']) {
			$estado_rol = "No se puede cambiar el ROL a un usuario con un mismo o mayor ROL que tu";
			$rol_disabled = "disabled";
		} else {
			$estado_rol = "Rol actual <b>{$rol}</b>";
			$rol_disabled = "";
		}
		$estado_activo = $usuario['activado'] ? "El usuario esta activado" : "El usuario no esta activado";
		$this->_vista->asignar('alert_tipo', $alert_tipo);
		$this->_vista->asignar('alert_mensaje', $alert_mensaje);
		$this->_vista->asignar('email', $usuario['email']);
		$this->_vista->asignar('rol_actual', $usuario['role']);
		$this->_vista->asignar('estado_rol', $estado_rol);
		$this->_vista->asignar('rol_disabled', $rol_disabled);
		$this->_vista->asignar('estado_activo', $estado_activo);
		$this->_vista->asignar('activado', $usuario['activado']);
		$this->_vista->asignar('check_state', $usuario['activado'] ? "checked" : "");
		$this->_vista->asignar('roles', $this->cargarRoles($usuario['role']));
	}

	public function guardar()
	{
		try {
			$this->_acl->acceso('general_acces');
			if ( $this->_token != $this->post('token') ) {
				throw new \Exception('Llave no valida!');
			}
			$mensajes = [];
			$sets = [];
			$id = $this->post("usuario_id");
			$usuario = $this->_modelo->buscarUsuario($id);
			$codigo = $usuario['codigo'];
			$estado_actual = $usuario['activado'];
			$rol_actual = $usuario['role'];
			if ( $password = $this->post("password") ) {
				$clave = password_hash($password, PASSWORD_BCRYPT, array("cost" => 10));
                array_push($sets, "clave = '{$clave}'");
                array_push($sets, "activado = 0");
				array_push($mensajes, "La Clave fue actualizada. Debes activar la cuenta por medio del link mas abajo");
			}
			$rol = $this->post("rol");
			if ( $rol_actual != $rol ) {
				array_push($sets, "role = {$rol}");
				array_push($mensajes, "Nuevo ROL: {$this->_modelo->obtenerCargo($rol)}");
				if ( $usuario['role'] == 5 ) {
					$this->_modelo->anularEjecutivo($id);
					array_push($mensajes, "El ROL como ejecutivo fue anulado!");
				}
			}
			$activar = $this->post("activar");
			if ( $activar ) {
				if ( $activar != $estado_actual ) {
					array_push($sets, "activado = 1");
					array_push($mensajes, "El Usuario fue activado");
				}
			} else {
				if ( $activar != $estado_actual ) {
					array_push($sets, "activado = 0");
					array_push($mensajes, "El Usuario fue desactivado");
				}
			}
			if ( $this->post("link") ) {
                $link = URL_BASE . "sistema/activar/{$id}/$codigo";
				array_push($mensajes, "Debes activar tu cuenta desde <a href='{$link}'>aquí</a> o bien, copiar la URL en la barra de direcciones de tu navegador {$link}");
            }
            $mensaje = "";
			if ( $sets && !$this->_modelo->actualizar($sets, $id) ) {
                throw new \Exception("Hubo un error al actualizar los datos del usuario!");
            }
            if ( count($mensajes) || $this->post("correo") || $this->post("link") ) {
                if ( count($mensajes) ) {
                    array_push($mensajes, "Cambios realizados por el Administrador {$this->_administrador['alias']}");
                    array_unshift($mensajes, "Se actualizaron los siguientes campos del usuario {$usuario['alias']}");
                }
                $adicional = nl2br($this->post('adicional'));
                if ( $this->enviar_correo($id, $mensajes, $adicional) ) {
                    Sesion::set("mensaje", "Correo enviado a <b>{$usuario['email']}</b>");
                }
            }
            if ( count($mensajes) ) {
                array_pop($mensajes);
                array_push($mensajes, "Correo enviado a <b>{$usuario['email']}</b>");
                $mensaje = implode("<br>", $mensajes);
				Sesion::set("mensaje", $mensaje);
            }
			$resultado['ok'] = true;
			$resultado['mensaje'] = $mensaje;
		} catch(\Exception $e) {
			$resultado['ok'] = false;
			$resultado['error'] = $e->getMessage();
		}
		header('Content-Type: application/json; charset=utf-8', true);
		echo json_encode($resultado);
		exit;
	}
  
    private function enviar_correo($id, array $mensajes = [], string $adicional = "")
    {
		$usuario = $this->_modelo->usuario($id);
		$funciones = $this->cargarLibreria('Funciones');
		$correo = $this->cargarLibreria('Correo');
		$correo->setFrom(CORREO_CONTACTO, NOMBRE_CONTACTO);
		$web = WEB_NAME;
        $fecha = $funciones->fecha();
        $message = "";
        if ( $adicional ) {
            $message = "<p>Saludos <b>{$usuario->alias}</b><br><br>{$adicional}</p>";
        } else {
            $message = "<p>Saludos <b>{$usuario->alias}</b></p>";
        }
        if ( count($mensajes) ) {
            $mensaje = implode("<br>", $mensajes);
            $message .= "<hr><p>Se han actualizado tus datos en <b>{$web}</b>, con fecha de {$fecha}</p>";
		    $message .= "<p>{$mensaje}</p>";
        }
		$to_user = $usuario->alias;
        $to_email = $usuario->email;
		$asunto = count($mensajes) ? "Actualización de datos (No Responder)" : "Mensaje Privado de {$this->_administrador['alias']}";
		$correo->addAddress($to_email, $to_user);
		$plantilla = [
			'ruta_img' => $this->_vista->setImagen('blockpc.png'),
			'nombre' => NOMBRE_CONTACTO,
			'correo' => CORREO_CONTACTO,
			'asunto' => $asunto,
			'mensaje' => $message,
            'fecha' => $funciones->fecha(date('Y-m-d')),
            'enviado_por' => "Mensaje enviado por <b>{$this->_administrador['alias']}</b>"
		];
		$PlantillaServer = $this->cargarVista('plantilla', $plantilla);
		if ( !PRODUCCION ) {
			echo $PlantillaServer;
			exit;
		} else {
			$correo->content($asunto, $PlantillaServer, $mensaje);
			if ( !$correo->send() ) {
				Sesion::set("error", "Hubo un inconveniente al enviar el correo. Contacta a un administrador");
				$this->redireccionar("usuarios/revisar/{$id}");
				exit;
			}
		}
		return true;
	}
  
	private function cargarRoles($idRole)
	{
		$miUsuario = Sesion::get('usuario');
		$roles = $this->_modelo->cargarRoles($miUsuario['role']);
		$vista = "<option value='0'>Seleccionar...</option>";
		foreach($roles as $role) {
			if ($role['id'] == $idRole) {
				$clase = "class='text-primary'";
				$selectedrol = "selected";
			} else {
				$clase = "";
				$selectedrol = "";
			}
			$vista .= "<option {$clase} value='{$role['id']}' {$selectedrol}>{$role['role']}</option>";
		}
		return $vista;
	}

}