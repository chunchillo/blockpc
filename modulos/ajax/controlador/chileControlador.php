<?php
/* Clase chileControlador.php */
namespace Ajax\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;

final class chileControlador extends Controlador
{
    private $_modelo;
    private $_token;

    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo('chile');
        $this->_token = $this->genToken();
    }

    public function index()
    {
        $this->redireccionar("sistema/login");
    }
	
	public function provincia()
	{
        try {
            $this->_acl->acceso('general_acces');
            if ( $this->_token != $this->post('token') ) {
                throw new \Exception('Llave no valida!');
            }
            $region = $this->post('region', 0);
            $id = 0;
            $provincias = $this->_modelo->provincias($region);
            $vista = "<option value='0'>Provincia...</option>";
            foreach( $provincias as $provincia ) {
                $selected = ( $id == $provincia->id ) ? "selected" : "";
                $vista .= "<option value='{$provincia->id}' {$selected}>{$provincia->nombre}</option>";
            }
            $resultado['ok'] = true;
            $resultado['vista'] = $vista;
        } catch(\Exception $e) {
            $resultado['ok'] = false;
            $resultado['error'] = $e->getMessage();
        }
        header('Content-Type: application/json; charset=utf-8', true);
        echo json_encode($resultado);
        exit;
	}
	
	public function comuna()
	{
        try {
            $this->_acl->acceso('general_acces');
            if ( $this->_token != $this->post('token') ) {
                throw new \Exception('Llave no valida!');
            }
            $provincia = $this->post('provincia', 0);
            $id = 0;
            $comunas = $this->_modelo->comunas($provincia);
            $vista = "<option value='0'>Comuna...</option>";
            foreach( $comunas as $comuna ) {
                $selected = ( $id == $comuna->id ) ? "selected" : "";
                $vista .= "<option value='{$comuna->id}' {$selected}>{$comuna->nombre}</option>";
            }
            $resultado['ok'] = true;
            $resultado['vista'] = $vista;
        } catch(\Exception $e) {
            $resultado['ok'] = false;
            $resultado['error'] = $e->getMessage();
        }
        header('Content-Type: application/json; charset=utf-8', true);
        echo json_encode($resultado);
        exit;
	}
}