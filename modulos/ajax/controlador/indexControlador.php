<?php
/* Clase indexControlador.php */
namespace Ajax\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;

final class indexControlador extends Controlador
{
    private $_modelo;
    private $_token;

    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo('index');
        $this->_token = $this->genToken();
    }

    public function index()
    {
        $this->redireccionar("sistema/login");
    }

    public function clave()
    {
        try {
            $this->_acl->acceso('general_acces');
            if ( filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING) != $this->_token ) {
                throw new \Exception("Token Invalido!");
            }
            $resultado['clave'] = $this->generarCodigo();
            $resultado['ok'] = 1;
            $resultado['mensaje'] = 'Clave generada automáticamente';
        } catch(\Exception $e) {
            $resultado['ok'] = 0;
            $resultado['error'] = $e->getMessage();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($resultado);
        exit;
    }
}