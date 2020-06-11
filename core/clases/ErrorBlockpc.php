<?php
/* Clase ErrorBlockpc.php */
namespace Blockpc\Clases;

final class ErrorBlockpc extends \Exception
{
    private $_error;
    private $_registro;
    private $_codigo;
    private $_codigos = [
        '400'  => 'La solicitud contiene sintaxis errónea. Consulte con el administrador.', # HTTP
        '401'  => 'Se necesitan credenciales de autenticación. Consulte con el administrador.', # HTTP
        '403'  => 'El servidor entendió la solicitud, pero se niega a cumplirla. Consulte con el administrador.', # HTTP
        '404'  => 'Recurso no encontrado. Consulte con el administrador.', # HTTP
        '500'  => 'Ha ocurrido un error interno. Consulte con el administrador.', # HTTP
        '1000' => 'Código de error no encontrado. Consulte con el administrador.',
        '1001' => 'No estas autorizado para acceder a esta pagina.', # Sesion
        '1002' => 'La clase ACL necesita un valor de tipo entero.', # ACL
        '1003' => 'No estas autorizado para acceder a esta pagina.', # ACL
        '1010' => 'Controlador no encontrado. Consulte con el administrador.', # Peticion
        '1020' => 'Clase no encontrada. Consulte con el administrador. <b>%s</b>', # Peticion
        '1030' => 'Error de conexión a la Base de Datos. Consulte con el administrador.', # Database
        '1040' => 'No existe la variable en el registro. Consulte con el administrador. <b>%s</b>', # Registro
        '1050' => 'Se esperaba un objeto Petición.', # Iniciar
        '1060' => 'Esta variable ya esta asignada para esta vista.', # Vista
        '1061' => 'Directorio de plantilla no encontrado.', # Plantilla
        '1062' => 'Archivo de cabecera de plantilla no encontrado.', # Plantilla
        '1063' => 'Archivo de menú de plantilla no encontrado.', # Plantilla
        '1064' => 'Archivo de pie de pagina de plantilla no encontrado.', # Plantilla
        '1065' => 'Archivo de navegación no encontrado.', # Plantilla
        '1066' => 'No existe la función buscada.', # Plantilla
        '1070' => 'No se encontró la vista. <b>%s</b>', # Vista
		'1071' => 'La imagen no existe. <b>%s</b>', # Vista
        '1080' => 'Error de carga de archivo CSS. <b>%s</b>', # Vista
        '1081' => 'Se esperaba un arreglo de archivos CSS.', # Vista
        '1090' => 'Error de carga de archivo JS.', # Vista
        '1091' => 'Se esperaba un arreglo de archivos JS.', # Vista
        '1092' => 'Se esperaba un arreglo de URLs.', # Vista
        '1093' => 'La URL no es valida.', # Vista
        '1094' => 'Este tipo de URL no es permitido.', # Vista
        '1100' => 'No existe el archivo para el modelo buscado. <b>%s</b>', # Controlador
        '1110' => 'Modelo no encontrada. <b>%s</b>', # Controlador
        '1120' => 'No se encontró la vista del controlador. <b>%s</b>', # Controlador
		'1121' => 'No se encontró la vista de plantilla. <b>%s</b>', # Controlador
        '1140' => 'Librería no encontrada. <b>%s</b>', # Controlador
        '2000' => 'Tiempo de sesión no definido.', # Sesion
        '2010' => 'Tiempo de sesión agotado.', # Sesion
        '2020' => 'El Rol no esta definido.', # Sesion
        '2021' => 'No estas autorizado para esta sección.', # Sesion
        '3000' => 'Error al crear un reporte!', # reportes
        '7000' => 'El modelo del Widget no se encuentra.', # Widgets
        '7001' => 'La vista del Widget no se encuentra.', # Widgets
        '7002' => 'No existe el Widget.', # Widgets
        '7003' => 'La clase del Widget no se encuentra.', # Widgets
        '7004' => 'El método del Widget no se encuentra.', # Widgets
    ];
    
    public function __construct($mensaje = null) {
        $this->_codigo = 1000;
        $this->_error = $mensaje;
        if ( $listado = array_merge( explode("/", $mensaje), array()) ) {
            $listado = array_filter(filter_var_array($listado,FILTER_SANITIZE_STRING));
            $codigo = strtolower(current($listado));
            if ( isset($this->_codigos[$codigo]) ) {
                $this->_codigo = array_shift($listado);
                $mensaje = implode("/", $listado);
                $mensaje = str_replace(["/", "\\"], "|", $mensaje);
                $this->_error = sprintf($this->getError($codigo), $mensaje);
            } else {
                $this->_codigo = 1000;
                $mensaje = implode("/", $listado);
                $mensaje = str_replace(["/", "\\"], "|", $mensaje);
                $this->_error = $mensaje;
            }
        }
		Sesion::set('codigo', $this->_codigo);
		Sesion::set('error', $this->_error);
		header("Location: " . URL_BASE . "error", true, 303);
		exit();
    }
  
    public function getError($codigo) {
        return $this->_codigos[$codigo];
    }

    public function getErrores() {
        return $this->_codigos;
    }

    public static function error_handler($number, $message, $filename, $line)
    {
        $str = "Error {$number}: {$message} in {$filename} at line {$line}";
        $today = date("Y-m-d");
        file_put_contents("logs/{$today}.txt", $str, FILE_APPEND);
    }


}