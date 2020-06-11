<?php
/* Clase registrarseControlador.php */
namespace Sistema\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;
use Blockpc\Librerias\Gump;

final class registrarseControlador extends Controlador
{
    private $_modelo;
    private $_token;
    private $_plantilla;

    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo('registrarse');
        $this->_token = $this->genToken();
        $this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
        $this->_vista->asignar('fecha', date('Y-m-d'));
        $this->_plantilla = PLANTILLA_POR_DEFECTO;
    }

    public function index()
    {
        try {
            $this->_vista->asignar('titulo', 'Registro Usuario');
            $this->_vista->asignar('token', $this->_token);

            $this->registro();

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
        $this->cargarPagina($this->_vista->renderizar('index', 'login', $this->_plantilla));
    }

    private function registro()
    {
        $this->_vista->asignar("alias", $this->post('alias'));
        $this->_vista->asignar("email", $this->post('email'));
        if ( $this->_token === filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING) )
        {
            $gump = new GUMP();
            // set validation rules
            $gump->validation_rules([
                'alias' => 'required|alpha_numeric|max_len,64|min_len,3',
                'clave' => 'required|max_len,24|min_len,6',
                'repetir' => 'required|max_len,24|min_len,6',
                'email' => 'required|valid_email'
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
                'repetir' => ['required' => 'Completa el campo Clave, es obligatorio.',
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
                'email' => 'trim|sanitize_email'
            ]);
            $valid_data = $gump->run($_POST);
            if ($gump->errors()) {
                // $this->dnl($gump->get_readable_errors()); // ['Field <span class="gump-field">Somefield</span> is required.'] 
                // $this->dnl($gump->get_errors_array()); // ['field' => 'Field Somefield is required']
                $error = implode("<br>", $gump->get_readable_errors());
                throw new \Exception($error);
            } else {
                if ( $this->post('clave') !== $this->post('repetir') ) {
                    throw new \Exception("Las claves deben ser iguales");
                }
                $alias = $this->post('alias');
                $correo = $this->post('email');
                if ( $this->_modelo->checkEmail($correo) ) {
                    throw new \Exception("El correo <b>{$correo}</b> ya existe!");
                }
                $usuario = [
                    'email' => $valid_data['email'],
                    'clave' => password_hash($valid_data['clave'], PASSWORD_BCRYPT, array("cost" => 10)),
                    'role' => 3,
                    'codigo' => $this->generarCodigo()
                ];
                $perfil = [
                    'alias' => $valid_data['alias'],
                    'user_id' => 0
                ];
                if ( $this->_modelo->registrar($usuario, $perfil) ) {
                    /*
                    Falta enviar correo
                    */
                    Sesion::set("mensaje", "Saludos <b>{$alias}</b><br>Necesitas validar tu cuenta por medio del correo enviado a <b>{$correo}</b>");
                    $this->redireccionar("sistema/login");
                }
            }
        }
    }
}