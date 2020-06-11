<?php
/* Clase listarControlador.php */
namespace Usuarios\Permisos\Controlador;

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

    public function index(int $pagina = 0): void
    {
		try {
			$this->_acl->acceso('admin_acces');
            $this->_vista->asignar('titulo', "Lista Permisos");
            $this->_vista->asignar('token', $this->_token);
            
            if ( !filter_var($pagina, FILTER_VALIDATE_INT) ) {
				$pagina = 1;
			}
			$tabla = $this->permisos($pagina);
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
        $this->_vista->setCSS(['permisos']);
		$this->cargarPagina($this->_vista->renderizar("index", "usuarios", $this->_plantilla));
	}
  
	private function permisos(int $pagina = 1, int $limite = 25, string $orden = 'ASC', string $campo = 'id', string $search = '')
	{
		$data = $this->data($orden, $campo, $search);
		$tabla = $this->cargarLibreria('TablaBlockpc', 'Permisos', 8);
        $tabla->setPermiso($this->_acl->accesoVista('admin_acces'));
        $tabla->setRegistrosPorPagina($limite);
		$tabla->setUrlBase($this->_urlModulo);
		$tabla->setRegistros($data);
		$tabla->setVistaTabla($this->_rutaVista . 'permiso.phtml');
		$tabla->procesar($pagina);
		return $tabla;
    }
    
    private function data(string $orden, string $campo, string $search, string $filtro = "")
	{
		## Search
		$idRole = Sesion::getUsuario('role');
        $searchValue = $search;
        $orderQuery = " {$filtro} ORDER BY {$campo} {$orden};";
		$searchQuery = " ";
		$searchArray = [];
		if ($searchValue != '') {
			$searchQuery = " AND ( permiso LIKE :permiso 
            OR llave LIKE :llave ) ";
            $searchArray = [ 
                'permiso' => "%{$searchValue}%", 
                'llave' => "%{$searchValue}%" 
            ];
		}
        $permisos = $this->_modelo->permisos($idRole, $searchQuery, $searchArray, $orderQuery);
		Sesion::destruir('data');
		Sesion::set('data', $permisos);
		$funciones = $this->cargarLibreria("Funciones");
        $contador = 0;
		foreach($permisos as &$permiso) {
            if ( $permiso['editable'] || $this->_acl->accesoVista('sudo_acces') ) {
				$permiso['url_editar'] = URL_BASE . "usuarios/permisos/editar/{$permiso['id']}";
				$permiso['url_eliminar'] = URL_BASE . "usuarios/permisos/eliminar/{$permiso['id']}";
				$permiso['no_editar'] = '';
				$permiso['no_eliminar'] = '';
			} else {
				$permiso['url_editar'] = "#";
				$permiso['url_eliminar'] = "#";
				$permiso['no_editar'] = 'disabled';
				$permiso['no_eliminar'] = 'disabled';
            }
            $permiso['descripcion'] = nl2br($permiso['descripcion']);
			$permiso['contador'] = ++$contador;
		}
		return $permisos;
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
			$campo = $this->post('campo') ?: 'id';
			$tabla = $this->permisos($pagina, $limite, $orden, $campo, $search);
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
}