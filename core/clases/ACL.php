<?php
/* ACL.php */
namespace Blockpc\Clases;

final class ACL extends ACLModelo
{
    private $_db;
    private $_id;
    private $_role;
    private $_permisos;

    public function __construct() {
        parent::__construct();
        $this->constructor();
    }
  
    private function constructor() {
        try {
			if ( !ACL ) {
				return null;
			}
			$this->_id = Sesion::getUsuario('id') ?: 0;
            $this->_role = $this->getModeloRole($this->_id);
            $this->_permisos = $this->getPermisoRole();
            $this->compilarAcl();
        } catch(\Exception $e) {
            throw new ErrorBlockpc("1002/{$e->getMessage()}");
        }
    }
  
    private function getPermisoRole() {
        $permisos = $this->getModeloPermisoRole($this->_role);
        $data     = array();
        for ( $i = 0; $i < count($permisos); $i++ ) {
            $llave = $this->getModeloPermisoLlave($permisos[$i]['idPermiso']);
            if ( $llave == '' ) continue;
            $v = ( $permisos[$i]['valor'] == 1 ) ? true : false;
            $data[$llave] = [
                'llave' => $llave,
                'permiso' => $this->getModeloPermisoNombre($permisos[$i]['idPermiso']),
                'valor' => $v,
                'heredado' => true,
                'id' => $permisos[$i]['idPermiso']
            ];
        }
        return $data;
    }
  
    private function getPermisosUsuario() {
        $ids = $this->getModeloPermisosRoleId($this->_role);
        if ( count($ids) ) {
            $estosIds = implode(",", $ids);
            $permisos = $this->getModeloPermisosUsuario($this->_id, $estosIds);
        } else {
            $permisos = array();
        }
        $data = array();
        for ( $i = 0; $i < count($permisos); $i++ ) {
            $llave = $this->getModeloPermisoLlave($permisos[$i]['idPermiso']);
            if ( $llave == '' ) continue;
            $v = ( $permisos[$i]['valor'] == 1 ) ? true : false;
            $data[$llave] = [
                'llave' => $llave,
                'permiso' => $this->getModeloPermisoNombre($permisos[$i]['idPermiso']),
                'valor' => $v,
                'heredado' => false,
                'id' => $permisos[$i]['idPermiso']
            ];
        }
        return $data;
    }
  
    private function compilarAcl() {
        $this->_permisos = array_merge($this->_permisos, $this->getPermisosUsuario() );
    }
  
    public function getPermisos() {
		if ( isset($this->_permisos) AND count($this->_permisos) ) {
			return $this->_permisos;
		}
	}
  
    private function permiso($llave) {
		if ( array_key_exists($llave, $this->_permisos) ) {
			if ( $this->_permisos[$llave]['valor'] == true OR $this->_permisos[$llave]['valor'] == 1 ) {
				return true;
			}
		}
		return false;
	}
  
  /*
   * FUNCION acceso
   * Valida el permiso del usuario para acceder a un controlador
   * @param String $llave Cadena de texto con el nombre del permiso
   * @return Boolean True si tiene los permisos, lanza un error en caso contrario
   */
	public function acceso($llave) {
		if ( $this->permiso($llave) ) {
			if ( !Sesion::get('recordarme') ) {
                $this->tiempo();
            }
			return true;
		}
		throw new ErrorBlockpc("1003");
	}
	
	/*
	* FUNCION accesoVista
	* Valida el permiso del usuario para acceder a una sección de la vista
	* @param String $llave Cadena de texto con el nombre del permiso
	* @return Boolean True si tiene los permisos, False en caso contrario
	*/
	public function accesoVista($llave) {
        if ( $this->_role == 1 || $this->permiso($llave) ) {
			return true;
		}
		return false;
	}
    
	/*
	* FUNCION accesoMenus
	* Valida el permiso del usuario para tener vista de un menú
	* @param String $llave Cadena de texto con el nombre del permiso
	* @return Boolean True si tiene los permisos, False en caso contrario
	*/
    public function accesoMenus($llave) {
		if ( $this->permiso($llave) ) {
			return true;
		}
		return false;
	}
	
	private function tiempo()
    {
        if( !Sesion::get('tiempo') || !defined('TIEMPO_SESION') ) {
            throw new ErrorBlockpc('2000'); // Tiempo de sesión no definido
        }
        if(TIEMPO_SESION == 0){
            return;
        }
        if( time() - Sesion::get('tiempo') > (TIEMPO_SESION * 60) ) {
            $this->cerrarSesion(Sesion::getUsuario('id'));
            Sesion::destruir();
            throw new ErrorBlockpc('2010'); // Tiempo de sesión agotado
            exit();
        }
        Sesion::set('tiempo', time());
    }

    public function is_sudo()
    {
        if ( $this->_role == 1 ) {
            return true;
        }
        return false;
    }

    public function is_admin()
    {
        if ( $this->_role == 1 || $this->_role == 2 ) {
            return true;
        }
        return false;
    }
  
}