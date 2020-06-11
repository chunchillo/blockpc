<?php
/* Clase Vista.php */
namespace Blockpc\Clases;

final class Vista extends Plantilla {
    private $_variables;
    private $_js;
    private $_css;

    public function __construct() {
        parent::__construct();
        $this->_variables = [];
        $this->_js = '';
        $this->_css = '';
    }

    /**
     * FUNCION renderizar
     * Genera una Vista
     * @param string $vista Una vista
     * @param string $id Un ID de menú
     * @param string $plantilla Una plantilla de HTML
     * @return string el string con la vista generada
     **/
    public function renderizar(string $vista, string $id = null, string $plantilla = PLANTILLA_POR_DEFECTO): string
    {
        $this->setPlantilla($plantilla);
        $rutaVista = $this->_rutas['vista'] . $vista . ".phtml";
        if ( !is_readable($rutaVista) ) {
            throw new ErrorBlockpc("1070/{$vista}");
        }
		$this->asignar('app_dominio', DOMINIO);
        $this->asignar('app_name', APP_NAME);
        $this->asignar('app_version', APP_VERSION);
        $this->asignar('app_sistema', APP_SISTEMA);
        $this->asignar('app_fecha', APP_FECHA);
        $this->asignar('url_base', URL_BASE);
        $this->asignar('url_assets', URL_BASE . 'assets/');
        $this->asignar('ruta_css', $this->_archivos['css']); # Ruta a CSSs de Plantilla
        $this->asignar('ruta_vendor', $this->_archivos['vendor']);
        $this->asignar('ruta_img', $this->_archivos['img']);
        $this->asignar('ruta_js', $this->_archivos['js']);
        $this->asignar('vista_img', $this->_rutas['img']); # Ruta a IMGs de módulos
        $this->asignar('vista_css', $this->_css);
        $this->asignar('vista_js', $this->_js);
        $this->asignar('menu_plantilla', $this->configurarMenu($id));
        ob_start();
        include_once($this->_cabecera);
        include_once($rutaVista);
        include_once($this->_footer);
        $contenido = ob_get_contents();
        ob_get_clean();
        return $contenido;
    }
  
    /**
     * FUNCION setImagen
     * Agrega una imagen a una vista
     * @param String $plantilla Una plantilla de HTML
     * @return String el string con la vista generada
     **/
    public function setImagen(string $imagen): string
	{
		$url = $this->_rutas['img'] . $imagen;
		$archivo = RUTA_MODULOS . 'vista' . DS . $this->_controlador . DS . 'img' . DS . $imagen;
		if ( !file_exists($archivo) )
			throw new ErrorBlockpc("1071/{$imagen}");
		return $url;
	}
  
    /**
     * FUNCION setCSS
     * Agrega CSS a las vistas
     * @param Array Arreglo con nombres de archivos css
     * @return Void Completa el arreglo privado de css
     **/
    public function setCSS(array $css): void
    {
        if ( is_array($css) && count($css) ) {
            for ( $i = 0; $i < count($css); $i++ ) {
                $archivo = RUTA_MODULOS . 'vista' . DS . $this->_controlador . DS . 'css' . DS . $css[$i] . '.css';
                if ( !file_exists($archivo) )
                    throw new ErrorBlockpc("1080/{$css[$i]}.css"); // Se esperaba un arreglo
                $url = $this->_rutas['css'] . $this->auto_version($archivo, 'css');
                $this->_css .= "<link rel='stylesheet' type='text/css' href='{$url}' />\n";
            }
        } else {
            throw new ErrorBlockpc("1081"); // Se esperaba un arreglo
        }
    }
  
    /**
     * FUNCION setJs
     * Agrega metodos de javascript a las vistas
     * @param Array Arreglo con nombres de archivos cjsss
     * @return Void Completa el arreglo privado de js
     **/
    public function setJS(array $js): void
    {
        if ( is_array($js) && count($js) ) {
            for ( $i = 0; $i < count($js); $i++ ) {
                $archivo = RUTA_MODULOS . 'vista' . DS . $this->_controlador . DS . 'js' . DS . $js[$i] . '.js';
                if ( !file_exists($archivo) )
                    throw new ErrorBlockpc("1090/{$js[$i]}.js"); // Se esperaba un arreglo
                $url = $this->_rutas['js'] . $this->auto_version($archivo, 'js');
                $this->_js .= "<script type='text/javascript' src='{$url}'></script>\n";
            }
        } else {
            throw new ErrorBlockpc("1091"); // Se esperaba un arreglo
        } 
    }
  
    /**
	 * FUNCION setURL
	 * Agrega scripts externos de javascript a las vistas
     * @param Array string con la url al documeto
     * @param String string con el tipo documento 'js' por defecto, o 'css'
     * @return Void Completa el arreglo privado de css o js
	 **/
	public function setURL(array $url, string $tipo = 'js'): void
    {
		if ( is_array($url) && count($url) ) {
			for ( $i = 0; $i < count($url); $i++ ) {
                if ( $tipo === "js" ) {
					$this->_js .= "<script type='text/javascript' src='{$url[$i]}'></script>\n";
                } else if ( $tipo === "css" ) {
					$this->_css .= "<link rel='stylesheet' type='text/css' href='{$url[$i]}' />\n";
                } else {
					throw new ErrorBlockpc("1094/{$tipo}"); // Se esperaba un arreglo
                }
			}
		} else {
			throw new ErrorBlockpc("1091"); // Se esperaba un arreglo
		} 
	}
	
	/**
	 *  Given a file, i.e. /css/base.css, replaces it with a string containing the
	 *  file's mtime, i.e. /css/base.1221534296.css.
	 *  
	 *  @param $file  The file to be loaded.  Must be an absolute path (i.e.
	 *                starting with slash).
	 **/
	private function auto_version(string $file, string $type = "css")
	{
		$mtime = filemtime($file);
		$newfile = preg_replace('{\\.([^./]+)$}', ".$mtime.\$1", $file);
		return basename($newfile);
	}
  
    /**
     * FUNCION asignar
     * Asigna variables a una vista
     * @param string $buscar string a buscar
     * @param string $reemplazar string a reemplazar
     **/
    public function asignar($buscar, $reemplazar): void
    {
        if ( array_key_exists($buscar, $this->_variables) ) {
            throw new ErrorBlockpc("Error 1060. {$buscar} no se puede asignar.");
        }
        if ( !empty($buscar) ) {
            $this->_variables[strtoupper($buscar)] = $reemplazar;
        }
    }
  
    /**
	 * FUNCION getVariables
     * Retorna las variables de una vista
     * @return Array
     **/
    public function getVariables(): array
    {
        return $this->_variables;
    }
}