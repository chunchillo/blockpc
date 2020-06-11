<?php
/* Peticion.php */
namespace Blockpc\Clases;

final class Peticion
{
	private $_rutaModulo;
    private $_urlModulo;
    private $_grupo;
    private $_modulo;
    private $_controlador;
    private $_clase;
    private $_metodo;
    private $_argumentos;
	
	const INDICE = "index";

    public function __construct() {
        $this->construirPeticion();
    }
	
	private function construirPeticion()
	{
		try {
			$url = filter_input(INPUT_GET, "url");
            $url = explode("/", $url);
            $url = array_filter(filter_var_array($url,FILTER_SANITIZE_STRING));
            $modulo = strtolower(current($url)) ?: self::INDICE;
            $this->_grupo = null;
			if ( $modulo != self::INDICE && is_dir(RUTA . 'modulos' . DS . $modulo . DS . self::INDICE) ) {
				$this->_grupo = strtolower(array_shift($url));
                $modulo = strtolower(current($url)) ?: self::INDICE;
			}
			if ( $this->_grupo ) {
                if ( is_dir(RUTA . 'modulos' . DS . $this->_grupo . DS . $modulo) ) {
					$this->_modulo = strtolower(array_shift($url)) ?: self::INDICE;
				} else {
					$this->_modulo = self::INDICE;
				}
            } else if ( is_dir(RUTA . 'modulos' . DS . $modulo) ) {
				$this->_modulo = strtolower(array_shift($url)) ?: self::INDICE;
			} else {
                $this->_modulo = self::INDICE;
            }
            if ( isset($this->_grupo) ) {
                $this->_rutaModulo =  RUTA . 'modulos' . DS . $this->_grupo . DS . $this->_modulo . DS;
                $this->_urlModulo = URL_BASE . "modulos/{$this->_grupo}/{$this->_modulo}/";
                $namespace = ucfirst($this->_grupo) . '\\' . ucfirst($this->_modulo);
            } else {
                $this->_rutaModulo = RUTA . 'modulos' . DS . $this->_modulo . DS;
                $this->_urlModulo = URL_BASE . "modulos/{$this->_modulo}/";
                $namespace = ucfirst($this->_modulo);
            }
			//$this->_rutaModulo = isset($this->_grupo) ? RUTA . 'modulos' . DS . $this->_grupo . DS . $this->_modulo . DS : RUTA . 'modulos' . DS . $this->_modulo . DS;
            //$this->_urlModulo = isset($this->_grupo) ? URL_BASE . "modulos/{$this->_grupo}/{$this->_modulo}/" : URL_BASE . "modulos/{$this->_modulo}/";
            $controlador = strtolower(current($url)) ?: self::INDICE;
            //$namespace = isset($this->_grupo) ? ucfirst($this->_grupo) . '\\' . ucfirst($this->_modulo) : ucfirst($this->_modulo);
            $this->_clase = "{$namespace}\\Controlador\\{$controlador}Controlador";
            if (class_exists($this->_clase)) {
                $this->_controlador = strtolower(array_shift($url)) ?: self::INDICE;
            } else {
                $this->_controlador = self::INDICE;
                $this->_clase = "{$namespace}\\Controlador\\indexControlador";
            }
            $metodo = strtolower(current($url));
            if(!is_callable([$this->_clase, $metodo])) {
                $this->_metodo = self::INDICE;
            } else {
                $this->_metodo = strtolower(array_shift($url));
            }
            $this->_argumentos = $url;
			define('RUTA_MODULOS', $this->_rutaModulo);
            define('URL_MODULOS', $this->_urlModulo);
			# $this->mostrarVariables();
		} catch(\Exception $e) {
			throw new ErrorBlockpc("1000/{$e->getMessage()}");
		}
	}
  
    public function mostrarVariables() {
		$variables = [
			'Clase' => $this->_clase,
			'Ruta' => RUTA_MODULOS,
			'URL' => URL_MODULOS,
			'Grupo' => $this->_grupo,
			'Modulo' => $this->_modulo,
			'Controlador' => $this->_controlador,
			'Método' => $this->_metodo,
			'Argumentos' => $this->_argumentos,
		];
        echo "<pre>"; print_r($variables); echo "</pre>"; exit;
    }

    public function getRutaModulo() {
        return RUTA_MODULOS;
    }
  
    public function getUrlModulo() {
        return URL_MODULOS;
    }

    public function getGrupo() {
        return $this->_grupo;
    }

    public function getModulo() {
        return $this->_modulo;
    }

    public function getClase() {
        return $this->_clase;
    }

    public function getControlador() {
        return $this->_controlador;
    }

    public function getMetodo() {
        return $this->_metodo;
    }

    public function getArgumentos(int $indice = 0) {
        $indice = filter_var($indice, FILTER_VALIDATE_INT);
        if ( !$indice ) {
            return $this->_argumentos;
        } else {
            $i = $indice - 1;
            return $this->_argumentos[$i] ?? null;
        }
    }
}