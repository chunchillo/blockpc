<?php
/* Clase Widgets.php */
namespace Blockpc\Clases;

class Widgets
{
  
    private function __construct() {}
    
    protected function cargarModelo($modelo)
    {
        $rutaModelo = RUTA_WIDGETS . 'modelos' . DS . $modelo . 'Modelo.php';
        if ( is_readable($rutaModelo) ) {
            include_once($rutaModelo);
            $clase = "Widget\\Modelo\\" . $modelo . 'Modelo';
            if ( class_exists($clase) ) {
                return new $clase;
            }
        }
        throw new ErrorBlockpc("7000/{$modelo}");
    }
    
    protected function renderizar($vista, $datos = array(), $ext = 'phtml')
    {
        $rutaVista = RUTA_WIDGETS . 'vistas' . DS . $vista . '.' . $ext;
        if ( is_readable($rutaVista) ) {
            ob_start();
            extract($datos);
            include_once($rutaVista);
            $contenido = ob_get_contents();
            ob_end_clean();
            return $contenido;
        }
        throw new ErrorBlockpc("7001/{$vista}");
    }
  
}