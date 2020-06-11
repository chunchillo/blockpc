<?php
/* Clase ajaxControlador.php */
namespace Usuarios\Index\Controlador;

use Blockpc\Clases\Controlador;

final class ajaxControlador extends Controlador
{
    private $_modelo;
    private $_token;

    public function __construct() {
        $this->construir();
        $this->_token = $this->genToken();
        $this->_modelo = $this->cargarModelo('ajax');
    }

    public function index() {
        $this->redireccionar('usuarios/perfil');
    }
    
    public function usuario()
    {
        try {
            $this->_acl->acceso('general_acces');
            if ( !$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ) {
                throw new \Exception("Identificador no valido!");
            }
            $usuario = $this->_modelo->usuarioId($id);
            if ( file_exists(RUTA_ARCHIVOS_USUARIOS . $usuario['alias'] . DS . $usuario['imagen']) ) {
                $usuario['ruta_imagen'] = URL_ARCHIVOS_USUARIOS . $usuario['alias'] . '/' . $usuario['imagen'];
            } else {
                $usuario['ruta_imagen'] = URL_ARCHIVOS_IMAGENES . 'blockpc150x75.png';
            }
            $usuario['subtitulo'] = "Perfil Usuario {$usuario['alias']}";
            $usuario['resumen'] = nl2br($usuario['resumen']);
			$funcion = $this->cargarLibreria('Funciones');
            $usuario['creado_el'] = $funcion->fecha($usuario['creado']);
            
            $resultado['ok'] = true;
            $resultado['usuario'] = $usuario;
            $resultado['perfil'] = $this->cargarVista('perfil', $usuario);
        } catch(\Exception $e) {
            $resultado['ok'] = false;
            $resultado['error'] = $e->getMessage();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($resultado);
        exit;
    }
  
}