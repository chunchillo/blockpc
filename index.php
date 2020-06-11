<?php
/* Proyecto Base - index.php */
define('DS', DIRECTORY_SEPARATOR);
define('RUTA', realpath(dirname(__FILE__)) . DS);
define('RUTA_CORE', RUTA . 'core' . DS);
define('RUTA_VENDOR', RUTA . 'vendor' . DS);

require_once(RUTA_CORE . 'configuracion.php');
require_once(RUTA_VENDOR . 'autoload.php');

use Blockpc\Clases\{Sesion, Registro, Peticion, Iniciar, ErrorBlockpc, Database, ACL};

try {
	Sesion::init();
    $registro = Registro::getInstancia();
	$registro->set('peticion', new Peticion());
	$registro->set('database', new Database());
	$registro->set('acl', new ACL());
	Iniciar::ejecutar($registro->get('peticion'));
} catch(\Throwable $e) {
    if ( !PRODUCCION ) {
		echo '<pre>'; print_r($e); echo '</pre>'; exit;
	}
	if ( !$e instanceof ErrorBlockpc ) {
        header("Location: " . URL_BASE . "error/{$e->getMessage()}");
    } else {
        echo '<pre><b>Clase</b>: '; print_r(get_class($e)); echo '</br>';
        echo '<b>Linea</b>: '; print_r($e->getLine()); echo '</br>';
        echo '<b>Archivo</b>: '; print_r($e->getFile()); echo '</br>';
        echo '<b>Mensaje</b>: '; print_r($e->getMessage()); echo '</pre>';
    }
}
?>