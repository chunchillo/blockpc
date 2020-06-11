<?php
/* Registro.php */
namespace Blockpc\Clases;

final class Registro
{
    private static $_instancia = [];
    private $_data = [];

    final private function __construct () {}
    final private function __clone() {}
    final private function __wakeup() {}

    public static function getInstancia() {
        if ( !self::$_instancia instanceof self ) {
            $miclase = __CLASS__;
            self::$_instancia = new $miclase;
        }
        return self::$_instancia;
    }

    public function set($nombre, $valor) {
        $this->_data[$nombre] = $valor;
    }

    public function get($nombre) {
        if ( !isset($this->_data[$nombre]) ) {
            throw new ErrorBlockpc("1040/{$nombre}");
        }
        return $this->_data[$nombre];
    }
}