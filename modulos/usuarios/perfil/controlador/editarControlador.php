<?php
/* Clase editarControlador.php */
namespace Usuarios\Perfil\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;
use Blockpc\Librerias\Gump;

final class editarControlador extends Controlador
{
    private $_modelo;
    private $_token;
    private $_plantilla;
    private $_errores;

    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo('editar');
        $this->_token = $this->genToken();
        $this->_plantilla = PLANTILLA_ADMINISTRADOR;
        $this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
        $this->_vista->asignar('fecha', date('Y-m-d'));
        $this->_errores = [];
    }
	
	public function index()
	{
		try {
			$this->_acl->acceso('general_acces');
            $this->_vista->asignar('titulo', 'Editar Perfil');
            $this->_vista->asignar('token', $this->_token);

            $this->validar();

            if ( count($this->_errores) ) {
                $errores = implode("<br>", $this->_errores);
                $error = $this->cargarHTML('error', ['error' => $errores], $this->_plantilla);
                $this->_vista->asignar('error', $error);
            }
		} catch(\Exception $e) {
			$error = $this->cargarHTML('error', ['error' => $e->getMessage()], $this->_plantilla);
            $this->_vista->asignar('error', $error);
        }
        $this->_vista->setCSS(['editar']);
        $this->_vista->setJS(['editar', 'chile']);
		$this->cargarPagina($this->_vista->renderizar('editar', 'perfil', $this->_plantilla));
    }
    
    private function validar()
    {
        $funcion = $this->cargarLibreria('Funciones');
        $objeto = $funcion->toOBject(Sesion::get('usuario'));
        $rutaImagen = RUTA_ARCHIVOS_USUARIOS . $objeto->alias;
        $this->_vista->asignar('id', $objeto->id);
        $this->_vista->asignar('alias', $objeto->alias);
        $this->_vista->asignar('email', $objeto->email);
        $this->_vista->asignar('nombre', $objeto->nombre);
        $this->_vista->asignar('apellido', $objeto->apellido);
        $this->_vista->asignar('rut', $objeto->rut);
        $this->_vista->asignar('telefono', $objeto->telefono);
        $this->_vista->asignar('celular', $objeto->celular);
        $this->_vista->asignar('direccion', $objeto->direccion);
		$this->_vista->asignar('regiones', $this->region($objeto->region ?: 0));
		$this->_vista->asignar('provincias', $this->setProvincia($objeto->region ?: 0, $objeto->provincia ?: 0));
		$this->_vista->asignar('comunas', $this->setComuna($objeto->provincia ?: 0, $objeto->comuna ?: 0));
        $this->_vista->asignar('resumen', $objeto->resumen);
        if ( $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING) ) {
            if ( $token != $this->_token ) {
                throw new \Exception("Token no valido");
            }
            $gump = new Gump('es');
            // set validation rules
            $gump->validation_rules([
                'id' => "required|min_numeric,{$objeto->id}|max_numeric,{$objeto->id}",
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
                $funciones = $this->cargarLibreria("Funciones");
                $alias = $this->post('alias');
                $correo = $this->post('email');
                $rut = $funciones->formatRUT($this->post('rut'));
                $clave = $this->post('clave');
                $codigo = $this->generarCodigo();
                if ( $clave && $clave !== $this->post('repetir') ) {
                    throw new \Exception("Las claves deben ser iguales");
                }
                if ( $rut && !$funciones->valida_rut($rut)) {
                    throw new \Exception("El RUT <b>{$rut}</b> parece no ser valido");
                }
                if ( $this->_modelo->checkAlias($alias, $objeto->id) ) {
                    throw new \Exception("El usuario <b>{$alias}</b> ya existe!");
                }
                if ( $this->_modelo->checkEmail($correo, $objeto->id) ) {
                    throw new \Exception("El correo <b>{$correo}</b> ya existe!");
                }
                if ( $this->_modelo->checkRut($rut, $objeto->id) ) {
                    throw new \Exception("El RUT <b>{$rut}</b> ya existe!");
                }
                $imagen = $this->cargarLibreria('Upload');
                $imagen->setArchivo($_FILES['imagen']);
                $error = $imagen->getError();
                if ( !$error ) { #UPLOAD_ERR_OK
                    $imagen->setAnchoAlto(600,400);
                    $imagen->setAnchoAltoMinimos(150,150);
                    $imagen->setPesoMinimo(2000000);
                    $imagen->setNombre($alias ?: $objeto->alias);
                    $imagen->setDirectorio($rutaImagen);
                    $imagen->guardar();
                    //$p['imagen'] = $imagen->getNombre();
                }
                if ( $alias != $objeto->alias ) {
                    $rutaAntigua = RUTA_ARCHIVOS_USUARIOS . $objeto->alias;
                    if ( !$error ) {
                        if ( !$this->delTree($rutaAntigua) ) {
                            array_push($this->_errores, "El Directorio '{$rutaAntigua}' no se pudo eliminar");
                        }
                    } else {
                        list($n, $ext) = explode('.', $objeto->imagen);
                        rename($rutaAntigua . DS . "{$objeto->imagen}", $rutaAntigua . DS . "{$alias}.{$ext}");
                        rename($rutaAntigua, $rutaImagen);
                    }
                }
                $usuario = [
                    'email' => $correo ? $valid_data['email'] : null,
                    'clave' => $clave ? password_hash($valid_data['clave'], PASSWORD_BCRYPT, array("cost" => 10)) : null,
                    'codigo' => $codigo,
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
                    'comuna' => $valid_data['comuna'] ?: null,
                    'resumen' => $valid_data['resumen'] ?: null,
                    'imagen' => $imagen->getNombre() ?: null
                ];
                $perfil = array_filter($perfil, function($v) { return !is_null($v); });
                if ( !$this->_modelo->actualizar($usuario, $perfil, $objeto->id) ) {
                    Sesion::set("error", "El perfil no se pudo actualizar");
                    $this->redireccionar("usuarios/perfil");
                }
                $ip = $funcion->get_client_ip();
                if ( $this->sesion($ip, $objeto->id) ) {
                    Sesion::set("mensaje", "El perfil fue actualizado correctamente");
                    $this->redireccionar("usuarios/perfil");
                }
            }
        }
    }

    private function delTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    private function sesion($ip, $idUsuario)
    {
        if ( !count($this->_errores)) {
            $usuario = $this->_modelo->usuario($idUsuario);
			$rutaImagenUsuario = RUTA_ARCHIVOS_USUARIOS . $usuario['alias'] . DS . $usuario['imagen'];
            if ( file_exists($rutaImagenUsuario) ) {
                $usuario['ruta_imagen'] = URL_ARCHIVOS_USUARIOS . "{$usuario['alias']}/{$usuario['imagen']}";
            } else {
                $usuario['ruta_imagen'] = URL_ARCHIVOS_IMAGENES . "blockpc.png";
            }
            # Re-Iniciamos la sesión
            $recordarme = Sesion::get('recordarme');
            Sesion::regenerarSesion(true, $recordarme, $ip);
            Sesion::destruir('usuario');
            Sesion::iniciarSesion($usuario, $recordarme, $ip);
            return true;
        }
        return false;
    }
	
	private function region(int $id = 0)
	{
		$regiones = $this->_modelo->region();
		$vista = "<option value='0'>Seleccione Región...</option>";
		foreach( $regiones as $region ) {
			$selected = ( $id == $region->id ) ? "selected" : "";
			$vista .= "<option value='{$region->id}' {$selected}>{$region->nombre}</option>";
		}
		return $vista;
	}
	
	private function setProvincia(int $region, $id = 0)
	{
		$provincias = $this->_modelo->provincia($region);
		$vista = "<option value='0'>Seleccione Provincia...</option>";
		foreach( $provincias as $provincia ) {
			$selected = ( $id == $provincia->id ) ? "selected" : "";
			$vista .= "<option value='{$provincia->id}' {$selected}>{$provincia->nombre}</option>";
		}
		return $vista;
	}
	
	private function setComuna(int $provincia, $id = 0)
	{
		$comunas = $this->_modelo->comuna($provincia);
		$vista = "<option value='0'>Seleccione Comuna...</option>";
		foreach( $comunas as $comuna ) {
			$selected = ( $id == $comuna->id ) ? "selected" : "";
			$vista .= "<option value='{$comuna->id}' {$selected}>{$comuna->nombre}</option>";
		}
		return $vista;
	}
}