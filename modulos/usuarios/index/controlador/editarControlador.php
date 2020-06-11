<?php
/* Clase editarControlador.php */
namespace Usuarios\Index\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;
use Blockpc\Librerias\Gump;

final class editarControlador extends Controlador
{
    private $_modelo;
    private $_token;
    private $_plantilla;
    private $_funciones;

    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo('editar');
        $this->_token = $this->genToken();
        $this->_plantilla = PLANTILLA_ADMINISTRADOR;
        $this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
        $this->_vista->asignar('fecha', date('Y-m-d'));
        $this->_funciones = $this->cargarLibreria('Funciones');
    }

    public function index(int $id = 0)
    {
        try {
            $this->_acl->acceso('general_acces');
            $this->_vista->asignar('titulo', 'Editar Usuario');
            $this->_vista->asignar('token', $this->_token);

            if ( !$id || !filter_var($id, FILTER_VALIDATE_INT) ) {
                Sesion::set("error", "Identificador no Valido!");
                $this->redireccionar("usuarios/activos");
            }
            $this->editar($id);

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
        $this->cargarPagina($this->_vista->renderizar('editar', 'usuarios', $this->_plantilla));
    }

    private function editar(int $id): void
    {
        if ( !$usuario = $this->_modelo->usuario($id) ) {
            Sesion::set("error", "No se pudo encontrar el usuario requerido!");
            $this->redireccionar("usuarios/activos");
        }
        $this->_vista->asignar('id', $usuario->id);
        $this->_vista->asignar('alias', $usuario->alias);
        $this->_vista->asignar('nombre', $usuario->nombre);
        $this->_vista->asignar('apellido', $usuario->apellido);
        $this->_vista->asignar('email', $usuario->email);
        $this->_vista->asignar('rut', $usuario->rut);
        $this->_vista->asignar('telefono', $usuario->telefono);
        $this->_vista->asignar('celular', $usuario->celular);
        $this->_vista->asignar('direccion', $usuario->direccion);
        $this->_vista->asignar('regiones', $this->regiones($usuario->region));
        $this->_vista->asignar('provincias', $this->provincias($usuario->region, $usuario->provincia));
        $this->_vista->asignar('comunas', $this->comunas($usuario->provincia, $usuario->comuna));
        if ($token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING) ) {
            if ( $this->_token != $token) {
                Sesion::set('error', "Llave del formulario no corresponde!");
                $this->redireccionar("usuarios/activos");
            }
            $gump = new Gump('es');
            // set validation rules
            $gump->validation_rules([
                'id' => "required|min_numeric,{$id}|max_numeric,{$id}",
                'alias' => 'required|alpha_numeric|max_len,64|min_len,3',
                'clave' => 'max_len,24|min_len,6',
                'repetir' => 'equalsfield,clave|max_len,24|min_len,6',
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
                'clave' => ['max_len' => 'El campo Clave debe ser de 24 caracteres máximo',
                    'min_len' => 'El campo Clave debe tener al menos 6 caracteres',
                ],
                'repetir' => ['max_len' => 'El campo Clave debe ser de 24 caracteres máximo',
                    'min_len' => 'El campo Clave debe tener al menos 6 caracteres',
                ],
                'email' => ['required' => 'Completa el campo Email, es obligatorio.',
                    'valid_email' => 'El Email no parece ser valido'
                ]
            ]);
            // set filter rules
            $gump->filter_rules([
                'id' => 'trim|sanitize_numbers',
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
                $correo = $this->post('email');
                $rut = $this->_funciones->formatRUT($this->post('rut'));
                $clave = $this->post('clave');
                if ( $clave && $clave !== $this->post('repetir') ) {
                    throw new \Exception("Las claves deben ser iguales");
                }
                if ( $rut && !$this->_funciones->valida_rut($rut)) {
                    throw new \Exception("El RUT <b>{$rut}</b> parece no ser valido");
                }
                if ( $this->_modelo->checkAlias($alias, $id) ) {
                    throw new \Exception("El usuario <b>{$alias}</b> ya existe!");
                }
                if ( $this->_modelo->checkEmail($correo, $id) ) {
                    throw new \Exception("El correo <b>{$correo}</b> ya existe!");
                }
                if ( $this->_modelo->checkRut($rut, $id) ) {
                    throw new \Exception("El RUT <b>{$rut}</b> ya existe!");
                }
                $usuario = [
                    'email' => $correo ? $valid_data['email'] : null,
                    'clave' => $clave ? password_hash($valid_data['clave'], PASSWORD_BCRYPT, array("cost" => 10)) : null,
                    'user_id' => Sesion::getUsuario('id')
                ];
                $usuario = array_filter($usuario, function($v) { return !is_null($v); });
                $perfil = [
                    'alias' => $alias ? $valid_data['alias'] : null,
                    'nombre' => $valid_data['nombre'] ?: null,
                    'apellido' => $valid_data['apellido'] ?: null,
                    'rut' => $rut ?: null,
                    'telefono' => $valid_data['telefono'] ?: null,
                    'celular' => $valid_data['celular'] ?: null,
                    'direccion' => $valid_data['direccion'] ?: null,
                    'region' => $valid_data['region'] ?: null,
                    'provincia' => $valid_data['provincia'] ?: null,
                    'comuna' => $valid_data['comuna'] ?: null
                ];
                $perfil = array_filter($perfil, function($v) { return !is_null($v); });
                if ( !$this->_modelo->actualizar($usuario, $perfil, $id) ) {
                    Sesion::set("error", "El usuario <b>{$alias}</b> no se pudo actualizar");
                } else {
                    Sesion::set("mensaje", "El usuario <b>{$alias}</b> fue actualizado correctamente");
                }
                $this->redireccionar("usuarios/activos");
            }
        }
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