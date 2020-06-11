<?php
/* Iniciar.php */
namespace Blockpc\Clases;

final class Iniciar {

    final private function __construct () {}
    final private function __clone() {}
    final private function __wakeup() {}

    public static function ejecutar(Peticion $peticion) 
    {
        if ( !($peticion instanceof Peticion) ) {
            throw new ErrorBlockpc("1050");
        }
        $clase = $peticion->getClase();
        if( !class_exists($clase) ) {
            throw new ErrorBlockpc("1020/{$clase}");
        }
        $controlador = new $clase;
        try {
            if( count($peticion->getArgumentos()) ) {
                call_user_func_array([$controlador, $peticion->getMetodo()], $peticion->getArgumentos());
            } else {
                call_user_func([$controlador, $peticion->getMetodo()]);
            }
        } catch(\Throwable $e) {
            throw new ErrorBlockpc($e->getMessage());
        }
        
    }
}