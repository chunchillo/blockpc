<?php
/* Clase listarControlador.php */
namespace Usuarios\Roles\Controlador;

use Blockpc\Clases\Controlador;
use Blockpc\Clases\Sesion;

final class listarControlador extends Controlador
{
    private $_modelo;
    private $_token;
    private $_plantilla;

    public function __construct() {
        $this->construir();
        $this->_modelo = $this->cargarModelo("listar");
        $this->_token = $this->genToken();
        $this->_plantilla = PLANTILLA_ADMINISTRADOR;
        $this->_vista->asignar('error', '');
        $this->_vista->asignar('mensaje', '');
        $this->_vista->asignar('fecha', date('Y-m-d'));
    }
  
    public function index(int $pagina = 0)
    {
        try {
            $this->_acl->acceso('admin_acces');
            $this->_vista->asignar('titulo', "Roles Listado");
            $this->_vista->asignar('token', $this->_token);

            if ( !filter_var($pagina, FILTER_VALIDATE_INT) ) {
				$pagina = 1;
			}
			$tabla = $this->roles($pagina);
			$this->_vista->asignar('leyenda', $tabla->getLeyenda());
			$this->_vista->asignar('tbody', $tabla->getTbody());
            $this->_vista->asignar('paginacion', $tabla->getPaginador());

            if (Sesion::get("mensaje")) {
                $mensaje = $this->cargarHTML('mensaje', array('mensaje' => Sesion::get("mensaje")), $this->_plantilla);
                $this->_vista->asignar('mensaje', $mensaje);
                Sesion::destruir('mensaje');
            }
            if (Sesion::get("error")) {
                $error = Sesion::get("error");
                Sesion::destruir('error');
                throw new \Exception($error);
            }
        } catch(\Exception $e) {
            $error = $this->cargarHTML('error', array('error' => $e->getMessage()), $this->_plantilla);
            $this->_vista->asignar('error', $error);
        }
        $this->_vista->setURL([
			$this->cargarUrl("assets/printThis/printThis.js")
		], 'js');
        $this->_vista->setJS(['tabla', 'botones']);
        $this->_vista->setCSS(['roles']);
        $this->cargarPagina($this->_vista->renderizar("index", "usuarios", $this->_plantilla));
    }
    
    private function roles(int $pagina = 1, int $limite = 25, string $orden = 'ASC', string $campo = 'r.id', string $search = '')
	{
		$data = $this->data($orden, $campo, $search);
		$tabla = $this->cargarLibreria('TablaBlockpc', 'Roles', 8);
        $tabla->setPermiso($this->_acl->accesoVista('admin_acces'));
        $tabla->setRegistrosPorPagina($limite);
		$tabla->setUrlBase($this->_urlModulo);
		$tabla->setRegistros($data);
		$tabla->setVistaTabla($this->_rutaVista . 'role.phtml');
		$tabla->procesar($pagina);
		return $tabla;
    }
    
    private function data(string $orden, string $campo, string $search, string $filtro = "")
	{
		## Search
		$idRole = Sesion::getUsuario('role');
        $searchValue = $search;
        $orderQuery = " {$filtro} GROUP BY {$campo} {$orden} ";
		$searchQuery = " ";
		$searchArray = [];
		if ($searchValue != '') {
			$searchQuery = " AND ( r.role LIKE :role 
            OR r.descripcion LIKE :descripcion ) ";
            $searchArray = [ 
                'role' => "%{$searchValue}%", 
                'descripcion' => "%{$searchValue}%" 
            ];
		}
        $roles = $this->_modelo->roles($idRole, $searchQuery, $searchArray, $orderQuery);
		Sesion::destruir('data');
		Sesion::set('data', $roles);
		$funciones = $this->cargarLibreria("Funciones");
        $contador = 0;
		foreach($roles as &$role) {
            if ( $role['editable'] || $this->_acl->accesoVista('sudo_acces') ) {
                $role['url_editar'] = URL_BASE . "usuarios/roles/editar/{$role['id']}";
                $role['url_eliminar'] = URL_BASE . "usuarios/roles/eliminar/{$role['id']}";
                $role['no_editar'] = '';
                $role['no_eliminar'] = $role['total'] ? 'disabled' : '';
            } else {
                $role['url_editar'] = "#";
                $role['url_eliminar'] = "#";
                $role['no_editar'] = 'disabled';
                $role['no_eliminar'] = 'disabled';
            }
            $role['descripcion'] = nl2br($role['descripcion']);
            $role['contador'] = ++$contador;
		}
		return $roles;
	}
	
	public function tabla()
	{
		try {
			$this->_acl->acceso('general_acces');
			if ( $this->_token != $this->post('token') ) {
				throw new \Exception("Llave no valida!");
            }
            $pagina = $this->post('pagina') ?: 1;
			$limite = $this->post('limite') ?: 25;
			$search = $this->post('search') ?: '';
			$orden = $this->post('orden') ?: 'ASC';
			$campo = $this->post('campo') ?: 'r.id';
			$tabla = $this->roles($pagina, $limite, $orden, $campo, $search);
			$resultado['ok'] = true;
			$resultado['pagina'] = $pagina;
			$resultado['leyenda'] = $tabla->getLeyenda();
			$resultado['datos'] = $tabla->getTbody();
			$resultado['paginacion'] = $tabla->getPaginador();
		} catch(\Exception $e) {
			$resultado['ok'] = false;
			$resultado['error'] = $e->getMessage();
		}
		header('Content-Type: application/json; charset=utf-8', true);
		echo json_encode($resultado);
		exit;
	}
	
	public function setdata()
	{
		try {
			$this->_acl->acceso('general_acces');
			if ( $this->_token != $this->post('token') ) {
				throw new \Exception("Llave no valida!");
			}
			if ( !Sesion::get('data') ) {
				throw new \Exception("No hay datos que enviar!");
			}
			$resultado['ok'] = true;
			$resultado['clase'] = __CLASS__;
		} catch(\Exception $e) {
			$resultado['ok'] = false;
			$resultado['error'] = $e->getMessage();
		}
		header('Content-Type: application/json; charset=utf-8', true);
		echo json_encode($resultado);
		exit;
	}
  
    private function cargarRoles()
    {
        $roles = $this->_modelo->cargarRoles(Sesion::getUsuario('role'));
        $html = "";
        $contador = 0;
        foreach($roles as $role) {
            if ( $role['editable'] || $this->_acl->accesoVista('sudo_acces') ) {
                $role['url_editar'] = URL_BASE . "usuarios/roles/editar/{$role['id']}";
                $role['url_eliminar'] = URL_BASE . "usuarios/roles/eliminar/{$role['id']}";
                $role['no_editar'] = '';
                $role['no_eliminar'] = $role['total'] ? 'disabled' : '';
            } else {
                $role['url_editar'] = "#";
                $role['url_eliminar'] = "#";
                $role['no_editar'] = 'disabled';
                $role['no_eliminar'] = 'disabled';
            }
            $role['contador'] = ++$contador;
            $html .= $this->cargarVista('role', $role);
        }
        $this->_vista->asignar('roles', $html);
    }
}