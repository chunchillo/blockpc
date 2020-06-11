<?php
/* Controlador.php */
namespace Blockpc\Clases;

Abstract class Controlador {
	protected $_registro;
	protected $_peticion;
	protected $_acl;
	protected $_grupo;
    protected $_modulo;
    protected $_controlador;
    protected $_metodo;
    protected $_argumentos;
	protected $_vista;
	protected $_rutaVista;
	protected $_urlModulo;
	
	abstract public function index();

	/**
	 * FUNCIÖN construir
	 * Instancia variables del controlador
	 **/
    public function construir() {
		$this->_registro = Registro::getInstancia();
		$this->_peticion = $this->_registro->get('peticion');
		$this->_acl = $this->_registro->get('acl') ?? null;
		$this->_grupo = $this->_peticion->getGrupo();
        $this->_modulo = $this->_peticion->getModulo();
        $this->_controlador = $this->_peticion->getControlador();
        $this->_metodo = $this->_peticion->getMetodo();
        $this->_argumentos = $this->_peticion->getArgumentos();
		$this->_rutaVista = RUTA_MODULOS . "vista" . DS . $this->_controlador . DS;
		$this->_urlModulo = $this->_registro->get('peticion')->getUrlModulo();
		$this->_vista = new Vista();
	}
	
	/**
	 * FUNCIÖN cargarModelo
	 * Obtiene un objeto modelo
	 * @param String $modelo nombre del modelo
	 * @return Object instancia del modelo
	 **/
    protected function cargarModelo(string $modelo)
    {
        $rutaModelo = RUTA_MODULOS . "modelo" . DS . "{$modelo}Modelo.php";
        if ( !is_readable($rutaModelo) ) {
            throw new ErrorBlockpc("1100/{$modelo}");
        }
        $modulo = isset($this->_grupo) ? ucfirst($this->_grupo) . '\\' . ucfirst($this->_modulo) : ucfirst($this->_modulo);
        $clase = $modulo . "\\Modelo\\{$modelo}Modelo";
        require_once $rutaModelo;
        if ( !class_exists($clase) ) {
            throw new ErrorBlockpc("1110/{$modelo}Modelo");
        }
        return new $clase;
    }
  
	/**
	 * FUNCIÖN cargarVista
	 * Mostrar una Vista
	 * @param String $pagina nombre de la vista
	 * @param Array $variables arreglo asociativo
	 * @return html Contenido de la pagina procesado
	 **/
    protected function cargarVista(string $vista, array $variables = [])
    {
        $rutaVista = RUTA_MODULOS . "vista" . DS . $this->_controlador . DS . $vista . ".phtml";
        if ( !is_readable($rutaVista) ) {
            throw new ErrorBlockpc("1120/{$vista}");
        }
        ob_start();
        include $rutaVista;
        $pagina = ob_get_contents();
        ob_get_clean();
        return $this->reemplazar($pagina, $variables);
    }
  
    /**
     * FUNCIÖN cargarHTML
	 * Mostrar una Vista de una plantilla general
     * @param String $pagina nombre de la vista
     * @param Array $variables arreglo asociativo
     * @return html Contenido de la pagina procesado
     **/
    protected function cargarHTML(string $vista, array $variables = [], string $plantilla = PLANTILLA_POR_DEFECTO)
    {
        $rutaVista = RUTA_PLANTILLAS . $plantilla . DS . "html" . DS . $vista . ".phtml";
        if ( !is_readable($rutaVista) ) {
            throw new ErrorBlockpc("1120/{$vista}");
        }
        ob_start();
        include $rutaVista;
        $pagina = ob_get_contents();
        ob_get_clean();
        return $this->reemplazar($pagina, $variables);
    }
  
    /**
    * FUNCIÖN cargarPagina
	* Mostrar una vista formateada devuelta por Vista
    * @param String $vista nombre de la vista
    * @return html Contenido de la pagina procesado
    **/
    protected function cargarPagina(string $vista)
    {
        $variables = $this->_vista->getVariables();
        if ( Sesion::get('autorizado') ) {
            $variables['url_perfil'] = URL_BASE . 'usuarios/perfil';
            $variables['url_cerrar'] = URL_BASE . 'sistema/salir';
            $variables['url_imagen_perfil'] = Sesion::getUsuario('ruta_imagen');
            $variables['user_alias'] = Sesion::getUsuario('alias');
            $variables['user_cargo'] = Sesion::getUsuario('cargo');
            $variables['user_nombre'] = Sesion::getUsuario('nombre');
            $variables['user_apellido'] = Sesion::getUsuario('apellido');
        }
        $pagina = $this->reemplazar($vista, $variables);
        echo $pagina;
    }
  
    /**
    * FUNCIÓN cargarLibreria
	* Carga una clase Librería
    * @param String $libreria librería a llamar
    * @return Object Objeto de la clase librería llamada
    **/
    protected function cargarLibreria(string $libreria, ...$parametros)
    {
        $clase = "Blockpc\\Librerias\\{$libreria}";
        if( !class_exists($clase) ) {
            throw new ErrorBlockpc("1140/{$libreria}"); // Librería no encontrada
        }
        if (count($parametros)) {
            if (method_exists($clase,  '__construct') === false) {
                throw new ErrorBlockpc("El Constructor para la clase <b>{$clase}</b> no existe. No le deberías pasar argumentos a esta clase!");
            }
            $refMethod = new \ReflectionMethod($clase,  '__construct');
            $params = $refMethod->getParameters();
            $re_args = array();
            foreach($params as $key => $param) {
                if ($param->isPassedByReference()) {
                    $re_args[$key] = &$parametros[$key]; 
                } else {
                    $re_args[$key] = $parametros[$key] ?? null;
                }
            }
            $refClass = new \ReflectionClass($clase);
            $instancia = $refClass->newInstanceArgs((array) $re_args);
        } else {
            $instancia = new $clase;
        }
        return $instancia;
    }
    
    /**
    * FUNCIÖN cargarUrl
	* Retorna una url valida en el sistema
    * @param String $url parametro como url
    * @return String como url
    **/
    protected function cargarUrl(string $url)
	{
        return URL_BASE . $url;
    }
    
    /**
    * FUNCIÖN post
	* Retorna una variable post
    * @param string $clave una clave del array asociativo POST
    * @return mixed, un valor de la variable POST
    **/
    protected function post(string $clave = '', $valorPorDefecto = null)
    {
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if ( !$clave ) {
            return count($_POST) ? $_POST : false;
        }
        if ( !isset($_POST[$clave]) ) {
            return $valorPorDefecto ?? false;
        }
		if ( is_array($_POST[$clave]) ) {
			return count($_POST[$clave]) ? $_POST[$clave] : false;
		}
        return trim($_POST[$clave]);
    }

    /**
    * FUNCIÖN genToken
	* Genera un token de validación
    * @param String $token true, valida el token generado
    * @return String un token de validacion
    **/
	protected function genToken($token = false)
    {
		$agente = $_SERVER['HTTP_USER_AGENT'] ?? 'noUserAgent';
        $clave = $agente.$_SERVER['REMOTE_ADDR'];
        $valor = hash('sha512', $clave);
        if ( $token ) {
            return (filter_var($valor, FILTER_SANITIZE_NUMBER_INT) === $token);
        } else {
            return filter_var($valor, FILTER_SANITIZE_NUMBER_INT);
        }
	}
  
    /**
     * FUNCIÖN reemplazar
	 * Recibe una vista y reemplaza cadenas 
     * @param String $buffer vista de controlador
     * @param Array $variables Arreglo tipo variable => valor
     * @return tipo comentario
     **/
    private function reemplazar($buffer, $variables = array())
    {
        if ( count($variables) ) {
            foreach($variables as $clave => $valor) {
                $pos = strpos($buffer, '[' . strtoupper($clave) . ']');
                if ( $pos !== FALSE ) {
                    $buffer = str_replace('[' . strtoupper($clave) . ']', $valor, $buffer);
                }
            }
        }
        return $buffer;
    }
  
    /**
     * FUNCIÖN generarCodigo
	 * Genera un código aleatorio
     * @param Int $longitud largo de la cadena
     * @return String Cadena aleatoria generada
     **/
    protected function generarCodigo(int $longitud = 14)
    {
        $key = '';
        $patternAll = '1234567890@-_.abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = strlen($patternAll)-1;
        for($i=0; $i < $longitud; $i++) {
            $key .= $patternAll{mt_rand(0,$max)};
        }
        return $key;
    }

	/**
     * FUNCIÖN redireccionar
	 * redirecciona a una url interna 
     * @param String $ruta url
     **/
    protected function redireccionar(string $ruta = null)
    {
        if ( !$ruta ) {
            header("Location: " . URL_BASE, true, 303);
            exit();
        }
        header("Location: " . URL_BASE . "{$ruta}", true, 303);
        exit();
    }

	private function d($data)
	{
        $str = "<table style='border-bottom:0px solid #000; font-size: small; width: -webkit-fill-available;' cellpadding='5' cellspacing='0'>";
		if ( is_null($data) ) {
			$str .= "<tr>
                <td colspan='2'>NULL</td>
            </tr>";
		} elseif( $data == "" ) {
			$str .= "<tr>
                <td colspan='2'>EMPTY</td>
            </tr>";
		} elseif( is_array($data) ) {
			if ( count($data) == 0 ) {
                $str .= "<tr>
                    <td style='background-color:#008B8B; color:#FFF;border:1px solid #000;'>array</td>
                    <td colspan='2'>[]</td>
                </tr>";
			} else {
				foreach ($data as $key => $value) {
                    $str .= "<tr>
                        <td style='background-color:#008B8B; color:#FFF;border:1px solid #000;'>" . $key . "</td>
                        <td style='border:1px solid #000;'>" . $this->d($value) . "</td>
                    </tr>";
				}
			}
		} elseif ( is_object($data) ) {
			$str = $this->d(get_object_vars($data));
		} elseif ( is_bool($data) ) {
            $str .= "<tr>
                <td colspan='2'>" . ($data ? "TRUE" : "FALSE") . "</td>
            </tr>";
		} else {
            $len = mb_strlen($data);
            $type = gettype($data);
            $str .= "<tr>
                <td colspan='2'>" . preg_replace("/\n/", "<br>\n", $data) . " <small style='margin-left: 10px;'>[{$type}(" . $len . ")]<small></td>
            </tr>";
        }
        $str .= "</table>";
        return $str;
	}
    
    /**
     * FUNCIÖN dnl
	 * Muestra el debug del valor pasado sin terminar el script
     * @param mixed $data
     **/
	public function dnl($data){
		echo $this->d($data) . "<br>\n";
	}
    
    /**
     * FUNCIÖN dd
	 * Muestra el debug del valor pasado y termina el script
     * @param mixed $data
     **/
	public function dd($data){
		echo $this->dnl($data);
		exit;
    }
    
    private function display($var)
    {
        foreach($GLOBALS as $demo => $value) {
           if ($value === $var) {
              return $demo;
           }
        }
        return false;
     }

	public function ddt($message = ""){
		echo "[" . date("Y/m/d H:i:s") . "]" . $message . "<br>\n";
	}
}