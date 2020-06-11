<?php
/* Clase nuevoControlador.php */
namespace Usuarios\Index\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;
use Blockpc\Librerias\Gump;

final class nuevoControlador extends Controlador
{
    private $_modelo;
    private $_token;
    private $_plantilla;
    private $_funciones;

    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo('nuevo');
        $this->_token = $this->genToken();
        $this->_plantilla = PLANTILLA_ADMINISTRADOR;
        $this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
        $this->_vista->asignar('fecha', date('Y-m-d'));
		$this->_funciones = $this->cargarLibreria('Funciones');
    }
  
    public function index()
    {
        try {
            $this->_acl->acceso('admin_acces');
            $this->_vista->asignar('titulo', 'Nuevo Usuario');
            $this->_vista->asignar('token', $this->_token);
            $this->validar();
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
        $this->_vista->setJS(['nuevo']);
        $this->cargarPagina($this->_vista->renderizar("nuevo", "usuarios", $this->_plantilla));
    }
  
    private function validar()
    {
        $this->_vista->asignar('alias', $this->post('alias', ''));
        $this->_vista->asignar('roles', $this->roles($this->post('role', 3)));
        $this->_vista->asignar('nombre', $this->post('nombre', ''));
        $this->_vista->asignar('apellido', $this->post('apellido', ''));
        $this->_vista->asignar('email', $this->post('email', ''));
        $this->_vista->asignar('rut', $this->_funciones->formatRUT($this->post('rut', '')));
        $this->_vista->asignar('telefono', $this->post('telefono', ''));
        $this->_vista->asignar('celular', $this->post('celular', ''));
        $this->_vista->asignar('direccion', $this->post('direccion', ''));
        $this->_vista->asignar('resumen', $this->post('resumen', ''));
        $this->_vista->asignar('regiones', $this->regiones($this->post('region', 0)));
        $this->_vista->asignar('provincias', $this->provincias($this->post('region', 0), $this->post('provincia', 0)));
        $this->_vista->asignar('comunas', $this->comunas($this->post('provincia', 0), $this->post('comuna', 0)));
        if ($token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING) ) {
            if ( $this->_token != $token) {
                Sesion::set('error', "Llave del formulario no corresponde!");
                $this->redireccionar("usuarios/activos");
            }
            $gump = new Gump('es');
            // set validation rules
            $gump->validation_rules([
                'alias' => 'required|alpha_numeric|max_len,64|min_len,3',
                'clave' => 'required|max_len,24|min_len,6',
                'repetir' => 'required|equalsfield,clave|max_len,24|min_len,6',
                'email' => 'required|valid_email',
                'nombre' => 'alpha_space|max_len,64',
                'apellido' => 'alpha_space|max_len,64',
                'rut' => 'max_len,12|min_len,7',
                'telefono' => 'numeric|max_len,12|min_len,6',
                'celular' => 'numeric|max_len,12|min_len,6',
                'direccion' => 'max_len,128|min_len,3',
                'region' => 'min_numeric,0',
                'provincia' => 'min_numeric,0',
                'comuna' => 'min_numeric,0'
            ]);
            // set field-rule specific error messages
            $gump->set_fields_error_messages([
                'alias' => ['required' => 'Completa el campo Usuario, es obligatorio.',
                    'alpha_numeric' => 'El Usuario solo debe tener letras y numeros',
                    'max_len' => 'El campo Usuario debe ser de 64 caracteres máximo',
                    'min_len' => 'El campo Usuario debe tener al menos 3 caracteres',
                ],
                'clave' => ['required' => 'Completa el campo Clave, es obligatorio.',
                    'max_len' => 'El campo Clave debe ser de 24 caracteres máximo',
                    'min_len' => 'El campo Clave debe tener al menos 6 caracteres',
                ],
                'repetir' => ['required' => 'Confirma la Clave, es obligatorio.',
                    'max_len' => 'El campo Clave debe ser de 24 caracteres máximo',
                    'min_len' => 'El campo Clave debe tener al menos 6 caracteres',
                ],
                'email' => ['required' => 'Completa el campo Email, es obligatorio.',
                    'valid_email' => 'El Email no parece ser valido'
                ]
            ]);
            // set filter rules
            $gump->filter_rules([
                'alias' => 'trim|sanitize_string',
                'clave' => 'trim',
                'repetir' => 'trim',
                'email' => 'trim|sanitize_email',
                'nombre' => 'trim|sanitize_string',
                'apellido' => 'trim|sanitize_string',
                'rut' => 'trim|sanitize_string',
                'telefono' => 'trim|sanitize_numbers',
                'celular' => 'trim|sanitize_numbers',
                'direccion' => 'trim|sanitize_string',
                'region' => 'trim|whole_number',
                'provincia' => 'trim|whole_number',
                'comuna' => 'trim|whole_number'
            ]);
            $valid_data = $gump->run($_POST);
            if ($gump->errors()) {
                $error = implode("<br>", $gump->get_readable_errors());
                throw new \Exception($error);
            } else {
                $alias = $this->post('alias');
                $clave = $this->post('clave');
                $correo = $this->post('email');
                $codigo = $this->generarCodigo();
                $rut = $this->_funciones->formatRUT($this->post('rut'));
                if ( $this->post('clave') !== $this->post('repetir') ) {
                    throw new \Exception("Las claves deben ser iguales");
                }
                if ( $rut && !$this->_funciones->valida_rut($rut)) {
                    throw new \Exception("El RUT <b>{$rut}</b> parece no ser valido");
                }
                if ( $this->_modelo->checkAlias($alias) ) {
                    throw new \Exception("El usuario <b>{$alias}</b> ya existe!");
                }
                if ( $this->_modelo->checkEmail($correo) ) {
                    throw new \Exception("El correo <b>{$correo}</b> ya existe!");
                }
                if ( $this->_modelo->checkRut($rut) ) {
                    throw new \Exception("El RUT <b>{$rut}</b> ya existe!");
                }
                $usuario = [
                    'email' => $valid_data['email'],
                    'clave' => password_hash($clave, PASSWORD_BCRYPT, array("cost" => 10)),
                    'role' => 3,
                    'codigo' => $codigo
                ];
                $perfil = [
                    'alias' => $valid_data['alias'],
                    'nombre' => $valid_data['nombre'] ?: null,
                    'apellido' => $valid_data['apellido'] ?: null,
                    'rut' => $rut ?: null,
                    'telefono' => $valid_data['telefono'] ?: null,
                    'celular' => $valid_data['celular'] ?: null,
                    'direccion' => $valid_data['direccion'] ?: null,
                    'region' => $valid_data['region'] ?: null,
                    'provincia' => $valid_data['provincia'] ?: null,
                    'comuna' => $valid_data['comuna'] ?: null,
                    'resumen' => null,
                    'imagen' => null,
                    'user_id' => 0
                ];
                $perfil = array_filter($perfil, function($v) { return !is_null($v); });
                if ( !$user_id = $this->_modelo->nuevo($usuario, $perfil) ) {
                    throw new \Exception("No fue posible agregar al nuevo usuario <b>{$alias}</b>");
                }
                $mensaje = "Saludos Administrador<br>Se ha creado una cuenta de usuario para <b>{$alias}</b><br>La cuenta aparecera en <b>Usuarios No Activos</b>";
                if ( $this->enviarCorreo($user_id, $codigo, $clave) ) {
                    $mensaje .= "<br>Necesita validar su cuenta por medio del correo enviado a <b>{$correo}</b>";
                }
				if ( !PRODUCCION ) {
					$mensaje .= "<br>Clave <b>{$$clave}</b><br>";
                }
                Sesion::set("mensaje", $mensaje);
                $this->redireccionar("usuarios/activos");
            }
        }
    }
  
    private function enviarCorreo(int $idNuevo, string $codigo, string $pass)
    {
        $usuario = $this->_modelo->usuario($idNuevo);
        $datos = "";
        foreach ( $usuario as $clave => $valor ) {
            //$valor = ( $clave === "role" ) ? $this->_modelo->getRol($valor) : $valor;
            $datos .= "<b>" . ucwords($clave) ."</b>: {$valor}<br>";
        }
        $correo = $this->cargarLibreria('Correo');
		$correo->setFrom(CORREO_CONTACTO, NOMBRE_CONTACTO);
        $web = WEB_NAME;
        
        $url = URL_BASE . "usuarios/activar/{$idNuevo}/{$codigo}";
        $fecha = $this->_funciones->fecha($usuario->creado);
        $mensaje = "<p>Saludos <b>{$usuario->nombre}</b></p>";
        $mensaje .= "Enviaste una solicitud de usuario a <b>{$web}</b>, con fecha de {$fecha}<br>";
        $mensaje .= "Se ingresaron los siguientes datos:<pre>{$datos}</pre>";
        $mensaje .= "Necesitas activar la cuenta para poder ingresar al sistema y modificar tus datos.<br>";
        $mensaje .= "URL para activación: <a href='{$url}'>Activar Cuenta</a><br>";
        $mensaje .= "Datos para ingresar al sistema:<pre>Usuario: {$usuario->alias}<br>Clave: {$pass}</pre>";
        $mensaje .= "<em>Recuerda que puedes cambiar la clave desde tu perfil de usuario.</em>";
        $to_user = $usuario->alias;
        $to_email = $usuario->email;
        $asunto = "Nuevo usuario (No Responder)";
		$correo->addAddress($to_email, $to_user);
		$plantilla = [
			'ruta_img' => $this->_vista->setImagen('blockpc.png'),
			'nombre' => NOMBRE_CONTACTO,
			'correo' => CORREO_CONTACTO,
			'asunto' => $asunto,
			'mensaje' => $mensaje,
			'fecha' => $fecha
		];
		$PlantillaServer = $this->cargarVista('plantilla', $plantilla);
		if ( !PRODUCCION ) {
			echo $PlantillaServer;
			exit;
		} else {
			$correo->content($asunto, $PlantillaServer, $mensaje);
			if ( !$correo->send() ) {
				Sesion::set("error", "Hubo un inconveniente al enviar el correo. Contacta a un administrador");
				$this->redireccionar("usuarios/nuevo");
				exit;
			}
		}
        return true;
    }

    private function roles(int $idRole = 0): string
    {
        $roles = $this->_modelo->roles();
        $html = "";
        foreach($roles as $role) {
            if ($role->id == $idRole) {
                $html .= "<option value='{$role->id}' selected>{$role->role}</option>";
            } else {
                $html .= "<option value='{$role->id}'>{$role->role}</option>";
            }
        }
        return $html;
    }
	
	private function regiones(int $id = 0): string
	{
		$regiones = $this->_modelo->regiones();
		$vista = "<option value='0'>Región...</option>";
		foreach( $regiones as $region ) {
			$selected = ( $id == $region->id ) ? "selected" : "";
			$vista .= "<option value='{$region->id}' {$selected}>{$region->nombre}</option>";
		}
		return $vista;
	}
	
	private function provincias(int $region, $id = 0): string
	{
		$provincias = $this->_modelo->provincias($region);
		$vista = "<option value='0'>Provincia...</option>";
		foreach( $provincias as $provincia ) {
			$selected = ( $id == $provincia->id ) ? "selected" : "";
			$vista .= "<option value='{$provincia->id}' {$selected}>{$provincia->nombre}</option>";
		}
		return $vista;
	}
	
	private function comunas(int $provincia, $id = 0): string
	{
		$comunas = $this->_modelo->comunas($provincia);
		$vista = "<option value='0'>Comuna...</option>";
		foreach( $comunas as $comuna ) {
			$selected = ( $id == $comuna->id ) ? "selected" : "";
			$vista .= "<option value='{$comuna->id}' {$selected}>{$comuna->nombre}</option>";
		}
		return $vista;
	}
  
}