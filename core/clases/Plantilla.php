<?php
/* Plantilla.php */
namespace Blockpc\Clases;

class Plantilla {
	protected $_registro;
	protected $_peticion;
    protected $_controlador;
	protected $_rutas;
    protected $_acl;
    protected $_is_admin;
    protected $_is_sudo;
	
	public function __construct() {
		$this->_registro = Registro::getInstancia();
		$this->_peticion = $this->_registro->get('peticion');
		$this->_acl = $this->_registro->get('acl');
        $this->_controlador = $this->_peticion->getControlador();
		$this->setRutasVista();
	}
	
	/**
     * FUNCION setPlantilla
     * Configura una plantilla para las vistas
     * @param String tipo de plantilla
     * @return Void Configura variables privadas
     **/
    protected function setPlantilla(string $plantilla): void
    {
        $this->_rutaPlantilla = RUTA_PLANTILLAS . $plantilla . DS;
        if( !is_dir($this->_rutaPlantilla) ) {
            throw new \Exception("1061/{$plantilla}");
        }
        $this->_plantilla = $plantilla;
        $this->_archivos = [
            'img' => URL_PLANTILLAS . $this->_plantilla . "/img/",
            'js' => URL_PLANTILLAS . $this->_plantilla . "/js/",
            'css' => URL_PLANTILLAS . $this->_plantilla . "/css/",
            'vendor' => URL_PLANTILLAS . $this->_plantilla . "/vendor/",
        ];
        $this->_cabecera = $this->setCabecera();
        $this->_menu = $this->setMenu();
        $this->_footer = $this->setPieDePagina();
        $this->_is_admin = $this->_acl->is_admin();
        $this->_is_sudo = $this->_acl->is_sudo();
    }
	
	/**
     * FUNCION setCabecera
     * Configura la cabecera de la plantilla
     * @return Devuelve la ruta de la cabecera de la plantilla
     **/
    private function setCabecera() {
        $rutaCabecera = $this->_rutaPlantilla . 'cabecera.phtml';
        if( !is_readable($rutaCabecera) ) {
			// Archivo de cabecera de plantilla no encontrado
            throw new \Exception("1062/{$rutaCabecera}");
        }
        return $rutaCabecera;
    }

	/**
     * FUNCION setMenu
     * Configura el menú de la plantilla
     * @return Devuelve la ruta del menú de la plantilla
     **/
    private function setMenu() {
        $rutaMenu = $this->_rutaPlantilla . 'menu.phtml';
        if( !is_readable($rutaMenu) ) {
			// Archivo de menú de plantilla no encontrado
            throw new \Exception("1063/{$rutaMenu}");
        }
        return $rutaMenu;
    }

	/**
     * FUNCION setPieDePagina
     * Configura el pie de pagina de la plantilla
     * @return Devuelve la ruta el pie de pagina de la plantilla
     **/
    private function setPieDePagina() {
        $rutaPieDePagina = $this->_rutaPlantilla . 'piePagina.phtml';
        if( !is_readable($rutaPieDePagina) ) {
        // Archivo de pie de pagina de plantilla no encontrado
            throw new \Exception("1064/{$rutaPieDePagina}");
        }
        return $rutaPieDePagina;
    }

	/**
     * FUNCION setRutasVista
     * Configura el pie de pagina de la plantilla
     * @return Devuelve la ruta el pie de pagina de la plantilla
     **/
    private function setRutasVista() {
        /* variable relacionadas a un controlador especifico */
        $this->_rutas = [
            'vista' => RUTA_MODULOS . 'vista' . DS . $this->_controlador . DS,
            'img' => URL_MODULOS . "vista/" . $this->_controlador . "/img/",
            'js' => URL_MODULOS . "vista/" . $this->_controlador . "/js/",
            'css' => URL_MODULOS . "vista/" . $this->_controlador . "/css/",
        ];
    }
	
	/**
     * FUNCION setRutasVista
     * Configura el pie de pagina de la plantilla
     * @return Devuelve la ruta el pie de pagina de la plantilla
     **/
    protected function configurarMenu(string $id) {
        $navegacion = $this->_rutaPlantilla . 'navegacion.php';
        if ( !is_readable($navegacion) ) {
            // Archivo de navegación no encontrado
            throw new \Exception("1065/{$navegacion}");
        }
        require_once($navegacion);
        if (!function_exists('getMenus')) {
            // No existe la función buscada
            throw new \Exception("1066/getMenus()");
        }
        $id = $id ?? $this->_controlador;
        $menus = getMenus(Sesion::getUsuario('role'));
        extract($menus);
        ob_start();
        include $this->_menu;
        $contenido = ob_get_contents();
        ob_get_clean();
        return $contenido;
    }
}