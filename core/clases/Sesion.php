<?php
/* Clase Sesion.php */
namespace Blockpc\Clases;

class Sesion {
    private static $_sesion;

    private function __construct(){}

    public static function init()
    {
        session_start();
        self::setSessionId();
    }
  
    private static function setSessionId() {
		self::$_sesion = session_id();
	}
  
    public static function getSessionId()
    {
		return self::$_sesion;
	}
  
    public static function set( string $clave, $valor )
    {
        if ( $clave != "tiempo" && isset($_SESSION[$clave]) ) {
			return false;
		}  
		if ( is_array($valor) ) {
			$_SESSION[$clave] = $valor;
			return true;
		}
        if ( !empty(filter_var($clave, FILTER_SANITIZE_STRING)) && !empty($valor) ) {
            $_SESSION[$clave] = $valor;
			return true;
        }
    }
  
    public static function get( string $clave )
    {
        if ( isset($_SESSION[$clave]) )
            return $_SESSION[$clave];
        return false;
    }
  
    public static function destruir( string $clave = null )
    {
		try {
            if ( $clave ) {
                if ( is_array($clave) ) {
                    for ( $i = 0; $i <= count($clave); $i++ ) {
                        if ( isset($_SESSION[$clave[$i]]) )
                            unset($_SESSION[$clave[$i]]);
                    }
                } else {
                    if ( isset($_SESSION[$clave]) )
                        unset($_SESSION[$clave]);
                }
            } else {
                session_unset();
                session_destroy();
				self::init();
            }
        } catch(\Exception $e) {
            throw new \Exception($e);
        }
	}
  
    public static function iniciarSesion($usuario, $recordarme, $ip)
    {
		self::regenerarSesion(true, $recordarme, $ip);
        self::set('inicioSesion', time());
        self::set('tiempo', time());
        self::set('autorizado', 1);
        self::set('recordarme', $recordarme);
        self::set('usuario', $usuario);
    }
	
    public static function regenerarSesion($recargar = false, $recordarme, $ip)
	{
        // This token is used by forms to prevent cross site forgery attempts
        if( !self::get('nonce') || $recargar ) {
            self::set( 'nonce', bin2hex(openssl_random_pseudo_bytes(32)) );
        }
        if( !self::get('IPaddress') || $recargar ) {
            self::set( 'IPaddress', $ip );
        }
        if( !self::get('userAgent') || $recargar ) {
            self::set( 'userAgent', $_SERVER['HTTP_USER_AGENT'] );
        }
		
        // Set current session to expire in 1 minute
        self::set('OBSOLETE', true);
        self::set('EXPIRES', time() + 60);
		
        // Create new session without destroying the old one
        session_regenerate_id(false);

        // Grab current session ID and close both sessions to allow other scripts to use them
        $newSession = session_id();
        session_write_close();

        // Set session ID to the new one, and start it back up again
        session_id($newSession);
        if ( $recordarme ) {
			self::set('recordarme', true);
            ini_set('session.cookie_lifetime', '864000');
        }
        self::init();

        // Don't want this one to expire
        self::destruir('OBSOLETE');
        self::destruir('EXPIRES');
    }
  
    public static function setUsuario(string $clave, string $valor)
    {
        $usuario = self::get('usuario');
        if ( array_key_exists($clave, $usuario) ) {
            return false;
        }
        $usuario[$clave] = $valor;
        self::destruir('usuario');
        self::set('usuario', $usuario);
        return true;
    }
  
    public static function getUsuario(string $clave)
    {
        if ( !$usuario = self::get('usuario') ?? [] || !array_key_exists($clave, $usuario) ) {
            return false;
        }
        return $usuario[$clave] ?? false;
    }
  
    public static function delUsuario(string $clave)
    {
        if ( !$usuario = self::get('usuario') ?? [] || !array_key_exists($clave, $usuario) ) {
            return false;
        }
        unset($usuario[$clave]);
        self::destruir('usuario');
        self::set('usuario', $usuario);
        return true;
    }
  
    private static function validarSesion()
    {
        if ( !self::get('autorizado') ) {
            header("Location: " . URL_BASE . "error/1001",TRUE,301);
			exit();
        }
    }

	/*
	* FUNCION tiempo
	* Valida el tiempo de sesión
	*/
    private static function tiempo()
    {
        if( !self::get('tiempo') || !defined('TIEMPO_SESION') ) {
            throw new ErrorBlockpc('2000'); // Tiempo de sesión no definido
			exit();
        }
        if(TIEMPO_SESION == 0){
            return;
        }
        if( time() - self::get('tiempo') > (TIEMPO_SESION * 60) ) {
            self::destruir();
            throw new ErrorBlockpc('2010'); // Tiempo de sesión agotado
            exit();
        }
        self::set('tiempo', time());
    }

	/*
	* FUNCION getNivel
	* Obtiene el nivel de Rol de Usuario
	* @param String nivel
	*/
    private static function getNivel(string $nivel)
    {
        $role['SuperAdministrador'] = 1;
        $role['Administrador'] = 2;
        $role['Usuario'] = 3;
        if ( !array_key_exists($nivel, $role) ) {
            throw new ErrorBlockpc('2020'); // El Rol no esta definido
            exit();
        } else {
            return $role[$nivel];
        }
    }

	/*
	* FUNCION acceso
	* Valida el acceso a un controlador
	* @param String nivel
	*/
    public static function acceso(string $nivel) {
        self::validarSesion();
        $usuario = self::get('usuario');
        if ( !self::get('recordarme') ) {
            Sesion::tiempo();
        }
        if ( self::getNivel($nivel) < $usuario['role']) {
            throw new ErrorBlockpc('2021'); // No estas autorizado para esta sección
            exit();
        }
    }

	/*
	* FUNCION acceso
	* Valida el acceso a una vista
	* @param String nivel
	*/
    public static function accesoVista( $nivel ) {
        if( !self::get('autorizado') ) {
            return false;
        }
        $usuario = self::get('usuario');
        if ( self::getNivel($nivel) < $usuario['role']) {
            return false;
        }
        return true;
    }
}